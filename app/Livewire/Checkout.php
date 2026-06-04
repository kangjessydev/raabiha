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
    public $couponCode = '';
    public $appliedCoupon = null;
    public $discountAmount = 0;
    
    public function mount()
    {
        $firstMethod = \App\Models\PaymentMethod::where('is_active', true)->first();
        if ($firstMethod) {
            $this->payment_method = $firstMethod->code;
        }
        
        $this->appliedCoupon = session('applied_coupon', null);
        
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
            $price = $item->variant && $item->variant->price !== null 
                ? $item->variant->price 
                : $item->product->price;
                
            $total += $price * $item->quantity;
        }
        return $total;
    }
    
    public function getTotalProperty()
    {
        $this->calculateDiscount();
        return max(0, $this->subtotal - $this->discountAmount) + $this->shipping_cost;
    }
    
    public function calculateDiscount()
    {
        $this->discountAmount = 0;
        if ($this->appliedCoupon) {
            if ($this->appliedCoupon['min_spend'] > 0 && $this->subtotal < $this->appliedCoupon['min_spend']) {
                $this->removeCoupon();
                session()->flash('coupon_error', 'Total belanja tidak memenuhi syarat minimum voucher.');
            } else {
                if ($this->appliedCoupon['discount_type'] === 'fixed') {
                    $this->discountAmount = $this->appliedCoupon['discount_value'];
                } else {
                    $this->discountAmount = $this->subtotal * ($this->appliedCoupon['discount_value'] / 100);
                    if ($this->appliedCoupon['max_discount'] > 0 && $this->discountAmount > $this->appliedCoupon['max_discount']) {
                        $this->discountAmount = $this->appliedCoupon['max_discount'];
                    }
                }
            }
        }
    }
    
    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->discountAmount = 0;
        session()->forget('applied_coupon');
    }
    
    public function applyCoupon()
    {
        if (empty($this->couponCode)) {
            session()->flash('coupon_error', 'Masukkan kode voucher terlebih dahulu.');
            return;
        }

        $coupon = \App\Models\Coupon::where('code', $this->couponCode)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->first();

        if (!$coupon) {
            session()->flash('coupon_error', 'Kode voucher tidak valid atau sudah kadaluarsa.');
            return;
        }

        if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
            session()->flash('coupon_error', 'Kode voucher sudah melewati batas penggunaan.');
            return;
        }

        $this->appliedCoupon = $coupon->toArray();
        session(['applied_coupon' => $this->appliedCoupon]);
        $this->couponCode = '';
        
        $this->dispatch('close-voucher-sheet');
    }

    public function selectCoupon($code)
    {
        $this->couponCode = $code;
        $this->applyCoupon();
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
                'discount' => $this->discountAmount,
                'grand_total' => $this->total,
                'notes' => $this->notes,
            ]);
            
            // Mark coupon as used if applied
            if ($this->appliedCoupon) {
                $couponModel = \App\Models\Coupon::where('code', $this->appliedCoupon['code'])->first();
                if ($couponModel) {
                    $couponModel->increment('used_count');
                }
                session()->forget('applied_coupon');
            }

            foreach ($items as $item) {
                $price = $item->variant && $item->variant->price !== null 
                    ? $item->variant->price 
                    : $item->product->price;

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
                    $price = $item->variant && $item->variant->price !== null 
                        ? $item->variant->price 
                        : $item->product->price;
                        
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
        
        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->get();
        
        return view('livewire.checkout', [
            'paymentMethods' => $paymentMethods,
            'availableCoupons' => $availableCoupons
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
