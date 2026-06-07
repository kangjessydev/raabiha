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
use Livewire\Attributes\Lazy;

#[Layout('components.layouts.app')]
#[Lazy]
class Checkout extends Component
{
    // Form fields
    public $email;
    public $phone;
    public $first_name;
    public $last_name;
    public $address;
    public $province;
    public $city;
    public $district;
    public $postal_code;
    public $notes;
    
    // Region Dropdown State
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    
    public $selectedProvinceId = '';
    public $selectedCityId = '';
    public $selectedDistrictId = '';
    
    // Checkout state
    public $shipping_cost = 20000;
    public $base_shipping_cost = 20000;
    public $payment_method = 'bank_transfer';
    public $shipping_method = 'jne_reg';
    
    // Coupon state
    public $voucherCode = '';
    public $appliedVoucher = null;
    public $discountAmount = 0;
    
    public function mount()
    {
        $firstMethod = \App\Models\PaymentMethod::where('is_active', true)->first();
        if ($firstMethod) {
            $this->payment_method = $firstMethod->code;
        }
        
        $this->appliedVoucher = session('applied_voucher', null);
        
        // Fetch provinces on load
        $this->fetchProvinces();
        
        $this->generateShippingRates();
    }
    
    public function fetchProvinces()
    {
        $response = \Illuminate\Support\Facades\Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        if ($response->successful()) {
            $this->provinces = $response->json();
        }
    }

    public function updatedSelectedProvinceId($value)
    {
        $this->cities = [];
        $this->districts = [];
        $this->selectedCityId = '';
        $this->selectedDistrictId = '';
        
        $province = collect($this->provinces)->firstWhere('id', $value);
        if ($province) {
            $this->province = $province['name'];
        }
        
        if ($value) {
            $response = \Illuminate\Support\Facades\Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$value}.json");
            if ($response->successful()) {
                $this->cities = $response->json();
            }
        }
    }

    public function updatedSelectedCityId($value)
    {
        $this->districts = [];
        $this->selectedDistrictId = '';
        
        $city = collect($this->cities)->firstWhere('id', $value);
        if ($city) {
            $this->city = $city['name'];
        }
        
        if ($value) {
            $response = \Illuminate\Support\Facades\Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$value}.json");
            if ($response->successful()) {
                $this->districts = $response->json();
            }
        }
    }
    
    public function updatedSelectedDistrictId($value)
    {
        $district = collect($this->districts)->firstWhere('id', $value);
        if ($district) {
            $this->district = $district['name'];
        }
        $this->calculateDynamicShipping();
    }
    
    public $shippingRates = [];

    public function calculateDynamicShipping()
    {
        if (!$this->selectedProvinceId) {
            $this->base_shipping_cost = 20000;
        } else {
            $provId = (int) $this->selectedProvinceId;
            $cityId = (int) ($this->selectedCityId ?? 0);
            
            $baseCost = 40000; // Default
            
            if ($provId == 32) {
                $baseCost = 10000; // Jabar
            } elseif ($provId == 31 || $provId == 36) {
                $baseCost = 15000; // DKI, Banten
            } elseif ($provId == 33 || $provId == 34) {
                $baseCost = 20000; // Jateng, DIY
            } elseif ($provId == 35) {
                $baseCost = 25000; // Jatim
            } elseif ($provId >= 51 && $provId <= 53) {
                $baseCost = 35000; // Bali, NTB, NTT
            } elseif ($provId >= 11 && $provId <= 19) {
                $baseCost = 40000; // Sumatera
            } elseif ($provId >= 61 && $provId <= 76) {
                $baseCost = 50000; // Kalimantan, Sulawesi
            } elseif ($provId >= 81) {
                $baseCost = 80000; // Maluku, Papua
            }
            
            $variance = ($cityId % 10) * 1000;
            $this->base_shipping_cost = $baseCost + $variance;
        }
        
        $this->generateShippingRates();
        
        // Update current shipping cost based on selected method
        $this->updatedShippingMethod($this->shipping_method);
    }
    
    public function generateShippingRates()
    {
        $this->shippingRates = [];
        
        if (!$this->selectedDistrictId) {
            $this->shipping_cost = 0;
            return;
        }

        $activeCouriers = \App\Models\ShippingMethod::where('is_active', true)->get();
        
        foreach ($activeCouriers as $courier) {
            $code = strtolower($courier->code);
            $cName = strtoupper($courier->name);
            
            // Reguler Service
            $this->shippingRates[] = [
                'id' => $code . '_reg',
                'courier_name' => $cName,
                'service_name' => $cName === 'JNE' ? 'Reguler' : 'Reguler',
                'duration' => '2-3 hari kerja',
                'price' => $this->base_shipping_cost,
                'logo' => $courier->logo,
            ];
            
            // Express Service
            $this->shippingRates[] = [
                'id' => $code . '_express',
                'courier_name' => $cName,
                'service_name' => $cName === 'JNE' ? 'YES' : 'Ekspres',
                'duration' => '1 hari kerja',
                'price' => $this->base_shipping_cost + 15000,
                'logo' => $courier->logo,
            ];
        }
        
        // Set default shipping method to the first available if not set or invalid
        if (empty($this->shipping_method) || !collect($this->shippingRates)->contains('id', $this->shipping_method)) {
            if (count($this->shippingRates) > 0) {
                $this->shipping_method = $this->shippingRates[0]['id'];
                $this->updatedShippingMethod($this->shipping_method);
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

    public function getTotalProperty()
    {
        $this->calculateDiscount();
        $baseForTotal = max(0, $this->subtotal - $this->reseller_discount);
        return max(0, $baseForTotal - $this->discountAmount) + $this->shipping_cost;
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

        $this->appliedVoucher = $voucher->toArray();
        session(['applied_voucher' => $this->appliedVoucher]);
        $this->voucherCode = '';
        
        $this->dispatch('close-voucher-sheet');
    }

    public function selectVoucher($code)
    {
        $this->voucherCode = $code;
        $this->applyVoucher();
    }

    public function processCheckout()
    {
        $this->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'address' => 'required|string',
            'selectedProvinceId' => 'required',
            'selectedCityId' => 'required',
            'selectedDistrictId' => 'required',
            'postal_code' => 'required|string',
        ], [
            'selectedProvinceId.required' => 'Silakan pilih provinsi.',
            'selectedCityId.required' => 'Silakan pilih kota/kabupaten.',
            'selectedDistrictId.required' => 'Silakan pilih kecamatan.',
        ]);

        $items = $this->checkoutItems;
        if ($items->count() === 0) {
            session()->flash('error', 'Keranjang belanja Anda kosong.');
            return;
        }

        DB::beginTransaction();

        try {
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $order = Order::create([
                'user_id' => auth()->id() ?? null,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'address' => $this->address,
                    'province' => $this->province,
                    'city' => $this->city,
                    'district' => $this->district,
                    'postal_code' => $this->postal_code,
                    'notes' => $this->notes,
                ],
                'courier' => $this->shipping_method,
                'shipping_cost' => $this->shipping_cost,
                'payment_method' => $this->payment_method,
                'subtotal' => $this->subtotal,
                'discount_total' => $this->discountAmount + $this->reseller_discount,
                'grand_total' => $this->total,
                'notes' => $this->notes,
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
                ]);

                // Reduce stock
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }
                
                // Delete the checkout item from cart
                $item->delete();
            }

            // Clear cart if empty
            $cart = $this->cart;
            if ($cart && $cart->items()->count() === 0) {
                $cart->delete();
            }

            // Clear session to prevent stale items on next visit
            session()->forget('checkout_item_ids');

            DB::commit();

            // Create Tripay Transaction
            $apiKey = env('TRIPAY_API_KEY');
            $privateKey = env('TRIPAY_PRIVATE_KEY');
            $merchantCode = env('TRIPAY_MERCHANT_CODE');
            $isProduction = env('TRIPAY_MODE', 'sandbox') === 'production';
            
            if ($apiKey && $privateKey && $merchantCode) {
                $endpoint = $isProduction 
                    ? 'https://tripay.co.id/api/transaction/create'
                    : 'https://tripay.co.id/api-sandbox/transaction/create';

                // Tripay requires a specific payment method code (e.g. BRIVA, QRIS)
                // We map our form selection to a default Tripay code for testing if needed
                // But ideally, the form should send the exact Tripay method code.
                $method = $this->payment_method;
                
                $merchantRef = $order->order_number;
                $amount = $order->grand_total;
                
                $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
                
                // Format order items for Tripay
                $orderItems = [];
                foreach ($cart->items as $item) {
                    $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                        
                    $orderItems[] = [
                        'sku' => $item->variant ? ($item->variant->sku ?? 'SKU-'.$item->id) : ($item->product->sku ?? 'SKU-'.$item->id),
                        'name' => $item->product->name,
                        'price' => $price,
                        'quantity' => $item->quantity,
                        'product_url' => url('/product/' . $item->product->slug),
                        'image_url' => asset('assets/images/placeholder.png'), // Simplified
                    ];
                }
                
                // Add shipping as an item
                if ($this->shipping_cost > 0) {
                    $orderItems[] = [
                        'sku' => 'SHIPPING',
                        'name' => 'Ongkos Kirim (' . strtoupper($this->shipping_method) . ')',
                        'price' => $this->shipping_cost,
                        'quantity' => 1,
                    ];
                }

                $data = [
                    'method'         => $method,
                    'merchant_ref'   => $merchantRef,
                    'amount'         => $amount,
                    'customer_name'  => $this->first_name . ' ' . $this->last_name,
                    'customer_email' => $this->email,
                    'customer_phone' => $this->phone,
                    'order_items'    => $orderItems,
                    'return_url'     => url('/order-success?order=' . $merchantRef),
                    'expired_time'   => (time() + (24 * 60 * 60)), // 24 hours
                    'signature'      => $signature
                ];

                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->post($endpoint, $data);

                if ($response->successful() && $response->json('success') === true) {
                    $tripayData = $response->json('data');
                    $order->update([
                        'payment_id' => $tripayData['reference'],
                        'payment_url' => $tripayData['checkout_url']
                    ]);
                    
                    return redirect()->away($tripayData['checkout_url']);
                } else {
                    Log::error('Tripay Create Transaction Error', ['response' => $response->json()]);
                    throw new \Exception('Gagal membuat transaksi pembayaran: ' . $response->json('message'));
                }
            }

            return redirect()->to('/order-success?order=' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan saat memproses pesanan Anda: ' . $e->getMessage());
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
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();
        $this->calculateDiscount(); // Ensure discount is calculated before render
        
        $availableVouchers = \App\Models\Voucher::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->get();
        
        return view('livewire.checkout', [
            'paymentMethods' => $paymentMethods,
            'availableVouchers' => $availableVouchers
        ])->title('Checkout');
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
