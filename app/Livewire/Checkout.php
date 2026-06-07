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
        
        // Fetch rates if selectedDestinationId already exists (unlikely on fresh load)
        $this->generateShippingRates();
    }
    

    
    // Direct Autocomplete Logic
    public function updatedSearchLocation($value)
    {
        if (strlen($value) < 3) {
            $this->destinationOptions = [];
            return;
        }

        $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
        if (!$apiKey) return;

        $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
            ->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $value
            ]);
            
        if ($response->successful()) {
            $this->destinationOptions = $response->json('data') ?? [];
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
        $originCity = \App\Models\SiteSetting::where('key', 'rajaongkir_origin_city')->value('value');
        
        if (!$apiKey || !$originCity) return;
        
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
                $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
                    ->asForm()
                    ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                        'origin' => $originCity,
                        'destination' => $this->selectedDestinationId,
                        'weight' => $totalWeight,
                        'courier' => $courierCode
                    ]);

                if ($response->successful()) {
                    $results = $response->json('data');
                    if (!empty($results)) {
                        foreach ($results as $cost) {
                            $courierName = $cost['name'];
                            $serviceName = strtoupper($cost['service']);
                            
                            // Filter by allowed_services if defined
                            if (!empty($allowedServices) && !in_array($serviceName, $allowedServices)) {
                                continue;
                            }
                            
                            $price = $cost['cost'] ?? 0;
                            $etd = $cost['etd'] ?? '';
                            
                            $this->shippingRates[] = [
                                'id' => $courierCode . '_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $serviceName)),
                                'courier_name' => $courierName,
                                'service_name' => $serviceName,
                                'duration' => $etd,
                                'price' => $price,
                                'logo' => $courier->logo,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log or ignore courier error
            }
        }
        
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
            'selectedDestinationId' => 'required',
            'postal_code' => 'required|string',
        ], [
            'selectedDestinationId.required' => 'Silakan pilih lokasi tujuan pengiriman (Kecamatan/Kota).',
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

            $shippingAddressData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'destination_id' => $this->selectedDestinationId,
                'destination_label' => $this->selectedDestinationLabel,
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

            // Create Payment Transaction (Xendit)
            $apiKey = \App\Models\SiteSetting::where('key', 'xendit_secret_key')->value('value') ?: env('XENDIT_SECRET_KEY');
            
            if ($apiKey && $this->payment_method != 'bank_transfer' && $this->payment_method != 'cod') {
                $endpoint = 'https://api.xendit.co/v2/invoices';
                
                $merchantRef = $order->order_number;
                $amount = $order->grand_total;
                
                // Format order items for Xendit
                $orderItems = [];
                // Refresh cart relation to be safe
                $cartItems = \App\Models\CartItem::whereIn('id', session('checkout_item_ids', []))->get();
                foreach ($cartItems as $item) {
                    $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                        
                    $orderItems[] = [
                        'name' => mb_substr($item->product->name, 0, 255), // Xendit item name limit
                        'price' => (int) $price,
                        'quantity' => $item->quantity,
                    ];
                }
                
                // Add shipping as an item
                if ($this->shipping_cost > 0) {
                    $orderItems[] = [
                        'name' => 'Ongkos Kirim (' . strtoupper($this->shipping_method) . ')',
                        'price' => (int) $this->shipping_cost,
                        'quantity' => 1,
                    ];
                }

                $data = [
                    'external_id'      => $merchantRef,
                    'amount'           => (int) $amount,
                    'description'      => 'Pembayaran Pesanan ' . $merchantRef,
                    'invoice_duration' => 86400, // 24 hours
                    'customer' => [
                        'given_names'   => $this->first_name,
                        'surname'       => $this->last_name,
                        'email'         => $this->email,
                        'mobile_number' => $this->phone,
                    ],
                    'success_redirect_url' => url('/order-success?order=' . $merchantRef),
                    'failure_redirect_url' => url('/checkout'),
                    'items' => $orderItems
                ];

                $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                    ->post($endpoint, $data);

                if ($response->successful()) {
                    $xenditData = $response->json();
                    $order->update([
                        'payment_id' => $xenditData['id'],
                        'payment_url' => $xenditData['invoice_url']
                    ]);
                    
                    return redirect()->away($xenditData['invoice_url']);
                } else {
                    Log::error('Xendit Create Invoice Error', ['response' => $response->json()]);
                    throw new \Exception('Gagal membuat transaksi pembayaran: ' . ($response->json('message') ?? 'Internal Error'));
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
