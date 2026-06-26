<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Checkout extends Component
{
    // Form fields
    public $email;
    public $phone;
    public $first_name;
    public $last_name;
    public $address;
    public $agree_terms = false;
    public $province;
    public $city;
    public $district;
    public $postal_code;
    public $notes;
    
    // Region Dropdown State removed, handled by Komerce Autocomplete
    
    // Checkout state
    public $shipping_cost = 20000;
    public $base_shipping_cost = 20000;
    public $payment_method = 'bank_transfer';
    public $shipping_method = 'jne_reg';
    
    // Komerce Integration (Direct Autocomplete)
    public $searchLocation = '';
    public $destinationOptions = [];
    public $selectedDestinationId = '';
    public $selectedDestinationLabel = '';
    public $locationError = null;
    
    // Coupon state
    public $voucherCode = '';
    public $appliedVoucher = null;
    public $discountAmount = 0;
    
    // Guest checkout state: null = undecided, true = continue as guest, false = logged in
    public $guest_mode = null;
    
    // Save address state
    public $save_address = true;

    public function mount()
    {
        $holidayMode = \App\Models\SiteSetting::where('key', 'store_holiday_mode')->value('value');
        if ($holidayMode) {
            $holidayMessage = \App\Models\SiteSetting::where('key', 'store_holiday_message')->value('value') ?? 'Mohon maaf, toko kami sedang libur. Checkout tidak dapat dilakukan saat ini.';
            session()->flash('error', $holidayMessage);
            return redirect('/cart');
        }

        if (auth()->check()) {
            $this->guest_mode = false;
            $user = auth()->user();
            $this->email = $user->email;
            $this->first_name = explode(' ', $user->name, 2)[0] ?? '';
            $this->last_name = explode(' ', $user->name, 2)[1] ?? '';
            // Pre-fill from primary address if exists
            $address = $user->addresses()->where('is_primary', true)->first() ?? $user->addresses()->first();
            if ($address) {
                $this->phone = $address->phone ?? '';
                $this->address = $address->full_address ?? '';
            }
        } else {
            $this->guest_mode = null; // Show the choice screen
        }
        $firstMethod = \App\Models\PaymentMethod::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('config->availability')
                    ->orWhere('config->availability', 'both')
                    ->orWhere('config->availability', 'online');
            })
            ->first();
        if ($firstMethod) {
            $this->payment_method = $firstMethod->code;
        }
        
        $this->appliedVoucher = session('applied_voucher', null);
        
        // Fetch rates if selectedDestinationId already exists (unlikely on fresh load)
        $this->generateShippingRates();
    }
    

    
    // Direct Autocomplete Logic
    public function updatedSearchLocation($value)
    {
        $this->locationError = null;
        $value = trim($value);
        if (strlen($value) < 4) {
            $this->destinationOptions = [];
            return;
        }

        $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
        if (!$apiKey) {
            $this->locationError = 'API Key RajaOngkir belum dikonfigurasi di Pengaturan Admin.';
            return;
        }

        $cacheKey = 'location_search_v1_' . md5(strtolower($value));

        if (cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                $this->destinationOptions = $cached;
                return;
            }
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
                ->timeout(10)
                ->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                    'search' => $value
                ]);
                
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                $this->destinationOptions = $data;
                if (!empty($data)) {
                    cache()->put($cacheKey, $data, now()->addDays(7));
                }
            } else {
                $this->destinationOptions = [];
                $json = $response->json();
                if (isset($json['meta']['message'])) {
                    if (str_contains(strtolower($json['meta']['message']), 'limit')) {
                        $this->locationError = 'Batas pencarian wilayah RajaOngkir (Daily Limit Exceeded) hari ini telah terlampaui. Silakan hubungi admin toko via WhatsApp untuk checkout manual.';
                    } else {
                        $this->locationError = 'Gagal memuat lokasi: ' . $json['meta']['message'];
                    }
                } else {
                    $this->locationError = 'Gagal menghubungi server kurir (Status ' . $response->status() . ').';
                }
            }
        } catch (\Exception $e) {
            $this->destinationOptions = [];
            $this->locationError = 'Masalah koneksi ke server RajaOngkir: ' . $e->getMessage();
        }
    }

    public function selectDestination($id, $label)
    {
        $this->selectedDestinationId = $id;
        $this->selectedDestinationLabel = $label;
        $this->searchLocation = '';
        $this->destinationOptions = [];
        
        $this->generateShippingRates();
    }
    
    public function updatedAddressMode($value)
    {
        $this->generateShippingRates();
    }
    
    public $shippingRates = [];

    public function calculateDynamicShipping()
    {
        // Just call generateShippingRates, base_shipping_cost calculation is now dynamic from Komerce
        $this->generateShippingRates();
    }
    
    public function generateShippingRates()
    {
        $this->shippingRates = [];
        
        if (!$this->selectedDestinationId) {
            $this->shipping_cost = 0;
            return;
        }

        $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
        $originCityRaw = \App\Models\SiteSetting::where('key', 'rajaongkir_origin_city')->value('value');
        if (!$apiKey || !$originCityRaw) return;
        $originCity = explode('::', $originCityRaw)[0];
        
        $activeCouriers = \App\Models\ShippingMethod::where('is_active', true)->get();
        if ($activeCouriers->isEmpty()) {
            return;
        }
        
        // Calculate total weight
        $totalWeight = 0;
        $cart = $this->cart;
        if ($cart) {
            foreach ($cart->items as $item) {
                $itemWeight = $item->product->weight > 0 ? $item->product->weight : 1000;
                $totalWeight += ($itemWeight * $item->quantity);
            }
        }
        if ($totalWeight == 0) $totalWeight = 1000;

        foreach ($activeCouriers as $courier) {
            $courierCode = $courier->code;
            $courierConfig = $courier->config ?? [];
            $allowedServices = [];
            
            // Parse allowed services if configured
            if (!empty($courierConfig['allowed_services'])) {
                if (is_array($courierConfig['allowed_services'])) {
                    $allowedServices = array_map('strtoupper', array_map('trim', $courierConfig['allowed_services']));
                } else {
                    $allowedServices = array_map('trim', explode(',', strtoupper($courierConfig['allowed_services'])));
                }
            }

            try {
                $cacheKey = 'ship_rate_v1_' . md5($originCity . '_' . $this->selectedDestinationId . '_' . $totalWeight . '_' . $courierCode);
                $results = null;

                if (cache()->has($cacheKey)) {
                    $cachedCosts = cache()->get($cacheKey);
                    if (is_array($cachedCosts)) {
                        $results = $cachedCosts;
                    }
                }

                if ($results === null) {
                    $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
                        ->asForm()
                        ->timeout(10)
                        ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                            'origin' => $originCity,
                            'destination' => $this->selectedDestinationId,
                            'weight' => $totalWeight,
                            'courier' => $courierCode
                        ]);

                    if ($response->successful()) {
                        $results = $response->json('data') ?? [];
                        if (!empty($results)) {
                            cache()->put($cacheKey, $results, now()->addHours(6));
                        }
                    }
                }

                if (!empty($results)) {
                        foreach ($results as $cost) {
                            $courierName = $courier->name;
                            $rawServiceName = strtoupper($cost['service']);
                            
                            $serviceName = $rawServiceName;
                            if (!empty($courierConfig['service_aliases']) && is_array($courierConfig['service_aliases'])) {
                                foreach ($courierConfig['service_aliases'] as $rawCode => $alias) {
                                    if (strtoupper($rawCode) === $rawServiceName) {
                                        $serviceName = $alias;
                                        break;
                                    }
                                }
                            }
                            
                            // Filter by allowed_services if defined
                            if (!empty($allowedServices) && !in_array($rawServiceName, $allowedServices)) {
                                continue;
                            }

                            // Filter using dynamic shipping rules (custom or global)
                            if (!$courier->shouldShowService($rawServiceName, $totalWeight, $originCityRaw, $this->selectedDestinationLabel)) {
                                continue;
                            }
                            
                            $price = $cost['cost'] ?? 0;
                            $etd = $cost['etd'] ?? '';

                            // Hitung potongan voucher untuk layanan ini jika voucher ongkir aktif
                            $discountedPrice = $price;
                            if ($this->appliedVoucher && $this->appliedVoucher['is_shipping_voucher']) {
                                $discountValue = $this->appliedVoucher['discount_type'] === 'fixed'
                                    ? $this->appliedVoucher['discount_amount']
                                    : $price * ($this->appliedVoucher['discount_amount'] / 100);

                                if ($this->appliedVoucher['max_discount'] > 0 && $discountValue > $this->appliedVoucher['max_discount']) {
                                    $discountValue = $this->appliedVoucher['max_discount'];
                                }

                                $discountedPrice = max(0, $price - $discountValue);
                            }
                            
                            $this->shippingRates[] = [
                                'id' => $courierCode . '|' . $rawServiceName . '|' . $price,
                                'courier_name' => $courierName,
                                'service_name' => $serviceName,
                                'duration' => $etd,
                                'price' => $price,
                                'original_price' => $price,
                                'discounted_price' => $discountedPrice,
                                'logo' => $courier->logo,
                            ];
                        }
                    }
            } catch (\Exception $e) {
                // Log or ignore courier error
            }
        }

        // Urutkan opsi pengiriman berdasarkan harga setelah diskon (dari termurah ke termahal)
        usort($this->shippingRates, function ($a, $b) {
            return $a['discounted_price'] <=> $b['discounted_price'];
        });
        
        // Set default shipping method to the first available if not set or invalid
        if (empty($this->shipping_method) || !collect($this->shippingRates)->contains('id', $this->shipping_method)) {
            if (count($this->shippingRates) > 0) {
                $this->shipping_method = $this->shippingRates[0]['id'];
                $this->updatedShippingMethod($this->shipping_method);
            } else {
                $this->shipping_cost = 0;
                $this->shipping_method = '';
            }
        }
    }

    public function getCartProperty()
    {
        if (auth()->check()) {
            return Cart::with(['items.product', 'items.variant.attributeOptions.attribute'])
                ->where('user_id', auth()->id())
                ->first();
        } else {
            return Cart::with(['items.product', 'items.variant.attributeOptions.attribute'])
                ->where('session_id', session()->getId())
                ->first();
        }
    }

    public function getCheckoutItemsProperty()
    {
        $cart = $this->cart;
        if (!$cart) return collect();
        
        $selectedIds = session('checkout_item_ids', []);
        
        if (empty($selectedIds)) {
            return $cart->items;
        }
        
        $filtered = $cart->items->filter(function($item) use ($selectedIds) {
            return in_array((string)$item->id, $selectedIds);
        });

        if ($filtered->isEmpty() && !empty($selectedIds)) {
            session()->forget('checkout_item_ids');
            return $cart->items;
        }

        return $filtered;
    }

    public function getSubtotalProperty()
    {
        $items = $this->checkoutItems;
        
        $total = 0;
        foreach ($items as $item) {
            $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                
            $total += $price * $item->quantity;
        }
        return $total;
    }
    public function getResellerDiscountProperty()
    {
        if (auth()->check() && auth()->user()->hasRole('reseller') && auth()->user()->reseller_status === 'active') {
            $discountPercent = \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 0;
            return $this->subtotal * ($discountPercent / 100);
        }
        return 0;
    }

    public function getPaymentFeeProperty(): int
    {
        if (!$this->payment_method) return 0;
        $pm = \App\Models\PaymentMethod::where('code', $this->payment_method)->first();
        if (!$pm || !$pm->config) return 0;
        
        $feeCustomer = $pm->config['fee_customer'] ?? ['flat' => 0, 'percent' => 0];
        $flat    = (int) ($feeCustomer['flat'] ?? 0);
        $percent = (float) ($feeCustomer['percent'] ?? 0);
        
        $base = max(0, $this->subtotal - $this->reseller_discount - $this->discountAmount) + $this->shipping_cost;
        $percentFee = $percent > 0 ? (int) ceil($base * ($percent / 100)) : 0;
        
        return $flat + $percentFee;
    }

    public function getTotalProperty()
    {
        $this->calculateDiscount();
        $baseForTotal = max(0, $this->subtotal - $this->reseller_discount);
        return max(0, $baseForTotal - $this->discountAmount) + $this->shipping_cost + $this->paymentFee;
    }
    
    public function calculateDiscount()
    {
        $this->discountAmount = 0;
        $baseTotalForVoucher = max(0, $this->subtotal - $this->reseller_discount);

        if ($this->appliedVoucher) {
            if ($this->appliedVoucher['min_purchase'] > 0 && $baseTotalForVoucher < $this->appliedVoucher['min_purchase']) {
                $this->removeVoucher();
                session()->flash('voucher_error', 'Total belanja tidak memenuhi syarat minimum voucher.');
            } else {
                if ($this->appliedVoucher['is_shipping_voucher']) {
                    // Logika jika voucher ongkir: max diskon sebesar biaya pengiriman
                    $discountValue = $this->appliedVoucher['discount_type'] === 'fixed' 
                        ? $this->appliedVoucher['discount_amount'] 
                        : $this->shipping_cost * ($this->appliedVoucher['discount_amount'] / 100);
                    
                    if ($this->appliedVoucher['max_discount'] > 0 && $discountValue > $this->appliedVoucher['max_discount']) {
                        $discountValue = $this->appliedVoucher['max_discount'];
                    }
                    
                    // Maksimal potongan adalah seharga ongkir itu sendiri
                    $this->discountAmount = min($discountValue, $this->shipping_cost);
                } else {
                    if ($this->appliedVoucher['discount_type'] === 'fixed') {
                        $this->discountAmount = $this->appliedVoucher['discount_amount'];
                    } else {
                        $this->discountAmount = $baseTotalForVoucher * ($this->appliedVoucher['discount_amount'] / 100);
                        if ($this->appliedVoucher['max_discount'] > 0 && $this->discountAmount > $this->appliedVoucher['max_discount']) {
                            $this->discountAmount = $this->appliedVoucher['max_discount'];
                        }
                    }
                }
            }
        }
    }
    
    public function removeVoucher()
    {
        $this->appliedVoucher = null;
        $this->discountAmount = 0;
        session()->forget('applied_voucher');
        $this->generateShippingRates();
    }
    
    public function applyVoucher()
    {
        if (empty($this->voucherCode)) {
            session()->flash('voucher_error', 'Masukkan kode voucher terlebih dahulu.');
            return;
        }

        $voucher = \App\Models\Voucher::where('code', $this->voucherCode)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->first();

        if (!$voucher) {
            session()->flash('voucher_error', 'Kode voucher tidak valid atau sudah kadaluarsa.');
            return;
        }

        if ($voucher->max_uses > 0 && $voucher->used_count >= $voucher->max_uses) {
            session()->flash('voucher_error', 'Kode voucher sudah melewati batas penggunaan.');
            return;
        }

        $cartQuantity = $this->checkoutItems->sum('quantity');
        if ($voucher->min_items > 0 && $cartQuantity < $voucher->min_items) {
            session()->flash('voucher_error', 'Minimal jumlah belanja tidak terpenuhi (' . $voucher->min_items . ' item).');
            return;
        }
        
        if ($voucher->exclude_resellers && auth()->check() && auth()->user()->hasRole('reseller')) {
            session()->flash('voucher_error', 'Maaf, voucher ini tidak berlaku untuk mitra Reseller.');
            return;
        }

        if (!empty($voucher->specific_users) && auth()->check()) {
            if (!in_array(auth()->user()->email, $voucher->specific_users)) {
                session()->flash('voucher_error', 'Voucher ini tidak berlaku untuk akun Anda.');
                return;
            }
        }

        if ($voucher->max_uses_per_user > 0) {
            $userUsageQuery = \App\Models\Order::where('voucher_id', $voucher->id)
                ->where(function ($q) {
                    $q->where('payment_status', '!=', 'cancelled')
                      ->where('status', '!=', 'cancelled');
                });

            if (auth()->check()) {
                $userUsageQuery->where(function ($q) {
                    $q->where('user_id', auth()->id())
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [auth()->user()->email]);
                });
            } else {
                if (!empty($this->email)) {
                    $userUsageQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [$this->email]);
                } else {
                    $userUsageQuery = null;
                }
            }

            if ($userUsageQuery && $userUsageQuery->count() >= $voucher->max_uses_per_user) {
                session()->flash('voucher_error', 'Anda sudah melebihi batas penggunaan untuk voucher ini (' . $voucher->max_uses_per_user . ' kali).');
                return;
            }
        }

        $this->appliedVoucher = $voucher->toArray();
        session(['applied_voucher' => $this->appliedVoucher]);
        $this->voucherCode = '';
        
        $this->generateShippingRates();
        $this->dispatch('close-voucher-sheet');
    }

    public function selectVoucher($code)
    {
        $this->voucherCode = $code;
        $this->applyVoucher();
    }

    public function processCheckout()
    {
        $rules = [
            'email' => auth()->check() ? 'nullable|email' : 'required|email',
            'phone' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'address' => 'required|string',
            'selectedDestinationId' => 'required',
            'agree_terms' => 'accepted',
        ];

        $messages = [
            'email.required' => 'Email wajib diisi untuk menerima rincian invoice dan notifikasi pesanan.',
            'selectedDestinationId.required' => 'Silakan pilih lokasi tujuan pengiriman (Kecamatan/Kota).',
            'agree_terms.accepted' => 'Anda harus menyetujui Syarat dan Ketentuan untuk melanjutkan.',
        ];

        $this->validate($rules, $messages);

        if (auth()->check() && empty($this->email) && empty($this->phone)) {
            $this->addError('email', 'Isi salah satu: Email atau No. WhatsApp.');
            return;
        }

        if ($this->appliedVoucher) {
            $voucher = \App\Models\Voucher::find($this->appliedVoucher['id']);
            if ($voucher) {
                if ($voucher->max_uses > 0 && $voucher->used_count >= $voucher->max_uses) {
                    session()->flash('error', 'Voucher yang Anda gunakan baru saja habis.');
                    return;
                }

                if ($voucher->max_uses_per_user > 0) {
                    $userUsageQuery = \App\Models\Order::where('voucher_id', $voucher->id)
                        ->where(function ($q) {
                            $q->where('payment_status', '!=', 'cancelled')
                              ->where('status', '!=', 'cancelled');
                        });

                    if (auth()->check()) {
                        $userUsageQuery->where(function ($q) {
                            $q->where('user_id', auth()->id())
                              ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [auth()->user()->email]);
                        });
                    } else {
                        $userUsageQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [$this->email]);
                    }

                    if ($userUsageQuery->count() >= $voucher->max_uses_per_user) {
                        session()->flash('error', 'Anda sudah melebihi batas penggunaan untuk voucher ini (' . $voucher->max_uses_per_user . ' kali).');
                        return;
                    }
                }
            }
        }

        $items = $this->checkoutItems;
        if ($items->count() === 0) {
            session()->flash('error', 'Keranjang belanja Anda kosong.');
            return;
        }

        // 1. Atomic Lock to prevent double submit (network/concurrency issues)
        $lockKey = 'checkout_lock_' . (auth()->id() ?? session()->getId());
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 30);

        if (!$lock->get()) {
            session()->flash('error', 'Pesanan Anda sedang diproses. Silakan tunggu beberapa saat.');
            return;
        }

        try {
            DB::beginTransaction();

            // 2. Validate quantity & Stock with lockForUpdate to prevent race conditions and negative quantity hacks
            foreach ($items as $item) {
                if ($item->quantity <= 0) {
                    throw new \Exception("Jumlah produk '{$item->product->name}' tidak valid (harus lebih besar dari 0).");
                }

                if ($item->product_variant_id) {
                    $variant = \App\Models\ProductVariant::where('id', $item->product_variant_id)
                        ->lockForUpdate()
                        ->first();
                    if (!$variant) {
                        throw new \Exception("Varian produk '{$item->product->name}' tidak ditemukan.");
                    }
                    if ($variant->stock < $item->quantity) {
                        throw new \Exception("Stok produk '{$item->product->name}' (Varian: {$variant->name}) tidak mencukupi. Tersedia: {$variant->stock}.");
                    }
                } else {
                    $product = \App\Models\Product::where('id', $item->product_id)
                        ->lockForUpdate()
                        ->first();
                    if (!$product) {
                        throw new \Exception("Produk '{$item->product->name}' tidak ditemukan.");
                    }
                    if ($product->stock < $item->quantity) {
                        throw new \Exception("Stok produk '{$product->name}' tidak mencukupi. Tersedia: {$product->stock}.");
                    }
                }
            }

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $districtValue = $this->selectedDestinationId && $this->selectedDestinationLabel 
                ? $this->selectedDestinationId . '::' . $this->selectedDestinationLabel 
                : null;
            
            $city = '';
            $province = '';
            if ($this->selectedDestinationLabel) {
                $parts = explode(', ', $this->selectedDestinationLabel);
                if (count($parts) >= 4) {
                    $city = $parts[2] ?? '';
                    $province = $parts[3] ?? '';
                }
            }

            $shippingAddressData = [
                'name' => trim($this->first_name . ' ' . $this->last_name),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'destination_id' => $this->selectedDestinationId,
                'destination_label' => $this->selectedDestinationLabel,
                'district' => $districtValue,
                'city' => $city,
                'province' => $province,
                'postal_code' => $this->postal_code,
                'notes' => $this->notes,
            ];

            $order = Order::create([
                'user_id' => auth()->id() ?? null,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => $shippingAddressData,
                'courier' => $this->shipping_method,
                'shipping_cost' => $this->shipping_cost,
                'payment_method' => $this->payment_method,
                'subtotal' => $this->subtotal,
                'discount_total' => $this->discountAmount + $this->reseller_discount,
                'grand_total' => $this->total,
                'notes' => $this->notes,
                'voucher_id' => $this->appliedVoucher ? $this->appliedVoucher['id'] : null,
            ]);
            
            // Mark voucher as used if applied
            if ($this->appliedVoucher) {
                $voucherModel = \App\Models\Voucher::where('code', $this->appliedVoucher['code'])->first();
                if ($voucherModel) {
                    $voucherModel->increment('used_count');
                }
                session()->forget('applied_voucher');
            }

            foreach ($items as $item) {
                $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                $purchasePrice = $item->variant ? ($item->variant->purchase_price ?? $item->product->purchase_price) : $item->product->purchase_price;

                $attributes = [];
                if ($item->variant) {
                    foreach ($item->variant->attributeOptions as $option) {
                        $attributes[$option->attribute->name] = $option->value;
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'total' => $price * $item->quantity,
                    'purchase_price' => $purchasePrice,
                ]);

                // Reduce stock & Log it (using locked instances to ensure correct stock logs and prevent race condition data leakage)
                if ($item->product_variant_id) {
                    $lockedVariant = \App\Models\ProductVariant::where('id', $item->product_variant_id)
                        ->lockForUpdate()
                        ->first();
                    $before = $lockedVariant->stock;
                    $lockedVariant->decrement('stock', $item->quantity);
                    $after = $before - $item->quantity;

                    \App\Models\StockLog::create([
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'type'               => 'out',
                        'quantity_before'    => $before,
                        'quantity_change'    => -$item->quantity,
                        'quantity_after'     => $after,
                        'reason'             => 'Sales',
                        'notes'              => 'Penjualan pesanan #' . $orderNumber,
                        'user_id'            => auth()->id(),
                    ]);
                } else {
                    $lockedProduct = \App\Models\Product::where('id', $item->product_id)
                        ->lockForUpdate()
                        ->first();
                    $before = $lockedProduct->stock;
                    $lockedProduct->decrement('stock', $item->quantity);
                    $after = $before - $item->quantity;

                    \App\Models\StockLog::create([
                        'product_id'      => $item->product_id,
                        'type'            => 'out',
                        'quantity_before' => $before,
                        'quantity_change' => -$item->quantity,
                        'quantity_after'  => $after,
                        'reason'          => 'Sales',
                        'notes'           => 'Penjualan pesanan #' . $orderNumber,
                        'user_id'         => auth()->id(),
                    ]);
                }
                
                // Delete the checkout item from cart
                $item->delete();
            }

            // Save address automatically if requested and user is authenticated
            if (auth()->check() && $this->save_address) {
                // Set all other addresses to non-primary
                \App\Models\UserAddress::where('user_id', auth()->id())->update(['is_primary' => false]);
                
                \App\Models\UserAddress::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'recipient_name' => trim($this->first_name . ' ' . $this->last_name),
                        'phone' => $this->phone,
                        'full_address' => $this->address,
                    ],
                    [
                        'title' => 'Alamat Utama',
                        'province' => $province,
                        'city' => $city,
                        'district' => $districtValue,
                        'postal_code' => $this->postal_code,
                        'is_primary' => true,
                    ]
                );
            }

            // Clear cart if empty
            $cart = $this->cart;
            if ($cart && $cart->items()->count() === 0) {
                $cart->delete();
            }

            // Clear session to prevent stale items on next visit
            session()->forget('checkout_item_ids');

            // Check Active Payment Gateway
            $activeGateway = \App\Models\SiteSetting::where('key', 'active_payment_gateway')->value('value') ?: 'tripay';

            if ($this->payment_method != 'tunai') {
                if ($activeGateway === 'tripay') {
                    // Create Payment Transaction (Tripay)
                    $apiKey = \App\Models\SiteSetting::where('key', 'tripay_api_key')->value('value') ?: env('TRIPAY_API_KEY');
                    $privateKey = \App\Models\SiteSetting::where('key', 'tripay_private_key')->value('value') ?: env('TRIPAY_PRIVATE_KEY');
                    $merchantCode = \App\Models\SiteSetting::where('key', 'tripay_merchant_code')->value('value') ?: env('TRIPAY_MERCHANT_CODE');
                    $mode = \App\Models\SiteSetting::where('key', 'tripay_mode')->value('value') ?: env('TRIPAY_MODE', 'sandbox');
                    
                    if ($apiKey && $privateKey && $merchantCode) {
                        $endpoint = $mode === 'production' 
                            ? 'https://tripay.co.id/api/transaction/create'
                            : 'https://tripay.co.id/api-sandbox/transaction/create';

                $method = $this->payment_method;
                $merchantRef = $order->order_number;
                $amount = (int) $order->grand_total; // Cast ke int agar cocok dengan payload & signature
                
                $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
                
                // Format order items for Tripay
                $orderItems = [];
                foreach ($order->items as $item) {
                    $orderItems[] = [
                        'sku' => $item->variant ? ($item->variant->sku ?? 'SKU-'.$item->id) : ($item->product->sku ?? 'SKU-'.$item->id),
                        'name' => mb_substr($item->product->name, 0, 50),
                        'price' => (int) $item->price,
                        'quantity' => $item->quantity,
                        'product_url' => url('/product/' . $item->product->slug),
                        'image_url' => asset('assets/images/placeholder.png'),
                    ];
                }
                
                // Add shipping as an item
                if ($this->shipping_cost > 0) {
                    $orderItems[] = [
                        'sku'      => 'SHIPPING',
                        'name'     => 'Ongkos Kirim (' . strtoupper($this->shipping_method) . ')',
                        'price'    => (int) $order->shipping_cost,
                        'quantity' => 1,
                    ];
                }

                // Add payment fee as an item if applicable
                // grand_total = subtotal - discount + shipping + paymentFee
                $paymentFee = (int) $order->grand_total
                    - (int) $order->subtotal
                    + (int) ($order->discount_total ?? 0)
                    - (int) $order->shipping_cost;

                if ($paymentFee > 0) {
                    $orderItems[] = [
                        'sku'      => 'PAYMENT-FEE',
                        'name'     => 'Biaya Layanan Pembayaran',
                        'price'    => $paymentFee,
                        'quantity' => 1,
                    ];
                }

                $data = [
                    'method'         => $method,
                    'merchant_ref'   => $merchantRef,
                    'amount'         => (int) $amount,
                    'customer_name'  => trim($this->first_name . ' ' . $this->last_name) ?: 'Guest',
                    'customer_email' => $this->email ?: 'noemail@example.com',
                    'customer_phone' => $this->phone ?: '080000000000',
                    'order_items'    => $orderItems,
                    'return_url'     => url('/order-success?order=' . $merchantRef),
                    'expired_time'   => (time() + (24 * 60 * 60)), // 24 hours
                    'signature'      => $signature
                ];

                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->post($endpoint, $data);

                        if ($response->successful() && $response->json('success')) {
                            $tripayData = $response->json('data');
                            $order->update([
                                'payment_id' => $tripayData['reference'],
                                'payment_url' => $tripayData['checkout_url']
                            ]);
                            
                            DB::commit();
                            $this->redirect($tripayData['checkout_url'], navigate: false);
                            return;
                        } else {
                            Log::error('Tripay Create Transaction Error', ['response' => $response->json()]);
                            throw new \Exception('Gagal membuat transaksi Tripay: ' . ($response->json('message') ?? 'Internal Error'));
                        }
                    }
                } elseif ($activeGateway === 'xendit') {
                    // Create Payment Transaction (Xendit)
                    $apiKey = \App\Models\SiteSetting::where('key', 'xendit_secret_key')->value('value') ?: env('XENDIT_SECRET_KEY');
                    
                    if ($apiKey) {
                        $endpoint = 'https://api.xendit.co/v2/invoices';
                        $merchantRef = $order->order_number;
                        $amount = $order->grand_total;
                        
                        // Format order items for Xendit
                        $orderItems = [];
                        $cartItems = \App\Models\CartItem::whereIn('id', session('checkout_item_ids', []))->get();
                        foreach ($cartItems as $item) {
                            $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                            $orderItems[] = [
                                'name' => mb_substr($item->product->name, 0, 255),
                                'price' => (int) $price,
                                'quantity' => $item->quantity,
                            ];
                        }
                        
                        if ($this->shipping_cost > 0) {
                            $orderItems[] = [
                                'name' => 'Ongkos Kirim (' . strtoupper($this->shipping_method) . ')',
                                'price' => (int) $this->shipping_cost,
                                'quantity' => 1,
                            ];
                        }
                        
                        if ($this->paymentFee > 0) {
                            $orderItems[] = [
                                'name' => 'Biaya Layanan',
                                'price' => (int) $this->paymentFee,
                                'quantity' => 1,
                            ];
                        }

                        $data = [
                            'external_id'      => $merchantRef,
                            'amount'           => (int) $amount,
                            'description'      => 'Pembayaran Pesanan ' . $merchantRef,
                            'invoice_duration' => 86400,
                            'customer' => [
                                'given_names'   => trim($this->first_name . ' ' . $this->last_name) ?: 'Guest',
                                'email'         => $this->email ?: 'noemail@example.com',
                                'mobile_number' => $this->phone ?: '080000000000',
                            ],
                            'success_redirect_url' => url('/order-success?order=' . $merchantRef),
                            'failure_redirect_url' => url('/checkout'),
                            'items' => $orderItems
                        ];

                        // Hanya tampilkan metode yang dipilih oleh pelanggan
                        if ($this->payment_method && $this->payment_method !== 'XENDIT_AUTO') {
                            $data['payment_methods'] = [$this->payment_method];
                        }

                        $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                            ->post($endpoint, $data);

                        // Fallback: If Xendit rejects the specific payment method, try again without restrictions
                        if (!$response->successful() && $response->json('error_code') === 'UNAVAILABLE_PAYMENT_METHOD_ERROR') {
                            Log::warning('Xendit Fallback triggered: Payment method ' . $this->payment_method . ' not active. Falling back to generic invoice.');
                            unset($data['payment_methods']);
                            $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                                ->post($endpoint, $data);
                        }

                        if ($response->successful()) {
                            $xenditData = $response->json();
                            $order->update([
                                'payment_id' => $xenditData['id'],
                                'payment_url' => $xenditData['invoice_url']
                            ]);
                            DB::commit();
                            $this->redirect($xenditData['invoice_url'], navigate: false);
                            return;
                        } else {
                            Log::error('Xendit Create Invoice Error', ['response' => $response->json()]);
                            throw new \Exception('Gagal membuat transaksi Xendit: ' . ($response->json('message') ?? 'Internal Error'));
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->to('/order-success?order=' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan saat memproses pesanan Anda: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    public function updatedShippingMethod($value)
    {
        if (!isset($this->base_shipping_cost)) {
            $this->base_shipping_cost = 20000;
        }
        
        $rate = collect($this->shippingRates)->firstWhere('id', $value);
        if ($rate) {
            $this->shipping_cost = $rate['price'];
        } else {
            // Fallback logic if shippingRates is empty (e.g. before JS init)
            if (str_ends_with($value, '_express')) {
                $this->shipping_cost = $this->base_shipping_cost + 15000;
            } else {
                $this->shipping_cost = $this->base_shipping_cost;
            }
        }
    }

    public function render()
    {
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('config->availability')
                    ->orWhere('config->availability', 'both')
                    ->orWhere('config->availability', 'online');
            })
            ->get();
        $this->calculateDiscount(); // Ensure discount is calculated before render
        
        $vouchers = \App\Models\Voucher::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->get();
            
        $availableVouchers = collect();
        $cartQuantity = $this->checkoutItems->sum('quantity');
        $baseTotalForVoucher = max(0, $this->subtotal - $this->reseller_discount);
        $user = auth()->user();
        
        foreach ($vouchers as $voucher) {
            // Cek voucher khusus user tertentu
            if (!empty($voucher->specific_users)) {
                if (!$user || !in_array($user->email, $voucher->specific_users)) {
                    continue; // Jangan tampilkan sama sekali
                }
            }
            
            $isEligible = true;
            $reason = '';
            
            if ($voucher->max_uses > 0 && $voucher->used_count >= $voucher->max_uses) {
                $isEligible = false;
                $reason = 'Sudah melewati batas penggunaan maksimal.';
            } elseif ($voucher->min_items > 0 && $cartQuantity < $voucher->min_items) {
                $isEligible = false;
                $reason = 'Minimal belanja ' . $voucher->min_items . ' item.';
            } elseif ($voucher->min_purchase > 0 && $baseTotalForVoucher < $voucher->min_purchase) {
                $isEligible = false;
                $reason = 'Minimal transaksi Rp' . number_format($voucher->min_purchase, 0, ',', '.') . '.';
            } elseif ($voucher->exclude_resellers && $user && $user->hasRole('reseller')) {
                $isEligible = false;
                $reason = 'Tidak berlaku untuk mitra Reseller.';
            }
            
            if ($isEligible && $voucher->max_uses_per_user > 0) {
                $userUsageQuery = \App\Models\Order::where('voucher_id', $voucher->id)
                    ->where(function ($q) {
                        $q->where('payment_status', '!=', 'cancelled')
                          ->where('status', '!=', 'cancelled');
                    });
                
                if ($user) {
                    $userUsageQuery->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [$user->email]);
                    });
                } else {
                    if (!empty($this->email)) {
                        $userUsageQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.email')) = ?", [$this->email]);
                    } else {
                        $userUsageQuery = null;
                    }
                }
                
                if ($userUsageQuery && $userUsageQuery->count() >= $voucher->max_uses_per_user) {
                    $isEligible = false;
                    $reason = 'Telah melewati batas penggunaan untuk akun Anda (' . $voucher->max_uses_per_user . ' kali).';
                }
            }
            
            $voucher->is_eligible = $isEligible;
            $voucher->ineligibility_reason = $reason;
            $availableVouchers->push($voucher);
        }
        
        return view('livewire.checkout', [
            'paymentMethods' => $paymentMethods,
            'availableVouchers' => $availableVouchers
        ])->layout('components.layouts.app', [
            'title' => 'Checkout',
            'robots' => 'noindex, nofollow'
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24 animate-pulse">
            <div class="h-12 bg-[#e5e2de] w-1/4 mb-16 hidden md:block"></div>
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16">
                <div class="flex flex-col gap-10">
                    <div class="h-64 bg-[#e5e2de] w-full"></div>
                    <div class="h-64 bg-[#e5e2de] w-full"></div>
                </div>
                <div class="h-96 bg-[#e5e2de] w-full"></div>
            </div>
        </div>
        HTML;
    }
}
