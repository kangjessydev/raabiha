<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Cart extends Component
{
    public $cart;

    public $selectedItems = [];
    public $selectAll = true;
    public $couponCode = '';
    public $appliedCoupon = null;

    public function mount()
    {
        $this->loadCart();
        if ($this->cart && $this->cart->items) {
            $this->selectedItems = $this->cart->items->pluck('id')->map(fn($id) => (string)$id)->toArray();
        }
        $this->appliedCoupon = session('applied_coupon', null);
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

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        session()->forget('applied_coupon');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = $this->cart->items->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === $this->cart->items->count();
    }

    public function loadCart()
    {
        if (auth()->check()) {
            $this->cart = \App\Models\Cart::with(['items.product', 'items.variant.attributeOptions.attribute'])
                ->where('user_id', auth()->id())
                ->where('is_buy_now', false)
                ->first();
        } else {
            $this->cart = \App\Models\Cart::with(['items.product', 'items.variant.attributeOptions.attribute'])
                ->where('session_id', session()->getId())
                ->whereNull('user_id')
                ->where('is_buy_now', false)
                ->first();
        }
    }

    public function incrementQuantity($itemId)
    {
        $item = \App\Models\CartItem::find($itemId);
        if ($item) {
            $item->increment('quantity');
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function decrementQuantity($itemId)
    {
        $item = \App\Models\CartItem::find($itemId);
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($itemId)
    {
        $item = \App\Models\CartItem::find($itemId);
        if ($item) {
            $item->delete();
            $this->loadCart();
            $this->selectedItems = array_values(array_diff($this->selectedItems, [(string)$itemId]));
            $this->updatedSelectedItems();
            $this->dispatch('cart-updated');
        }
    }
    
    public function proceedToCheckout()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Pilih minimal satu produk untuk di-checkout.');
            return;
        }
        
        session(['checkout_item_ids' => $this->selectedItems]);
        return $this->redirect('/checkout', navigate: true);
    }

    public function render()
    {
        $subtotal = 0;
        if ($this->cart && $this->cart->items) {
            foreach ($this->cart->items as $item) {
                if (in_array((string)$item->id, $this->selectedItems)) {
                    $price = $item->variant && $item->variant->price ? $item->variant->price : $item->product->price;
                    $subtotal += $price * $item->quantity;
                }
            }
        }

        $discountAmount = 0;
        if ($this->appliedCoupon) {
            if ($this->appliedCoupon['min_spend'] > 0 && $subtotal < $this->appliedCoupon['min_spend']) {
                $this->removeCoupon();
                session()->flash('coupon_error', 'Total belanja tidak memenuhi syarat minimum voucher.');
            } else {
                if ($this->appliedCoupon['discount_type'] === 'fixed') {
                    $discountAmount = $this->appliedCoupon['discount_value'];
                } else {
                    $discountAmount = $subtotal * ($this->appliedCoupon['discount_value'] / 100);
                    if ($this->appliedCoupon['max_discount'] > 0 && $discountAmount > $this->appliedCoupon['max_discount']) {
                        $discountAmount = $this->appliedCoupon['max_discount'];
                    }
                }
            }
        }
        
        $grandTotal = max(0, $subtotal - $discountAmount);
        
        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->get();

        return view('livewire.cart', [
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
            'availableCoupons' => $availableCoupons,
        ]);
    }
}
