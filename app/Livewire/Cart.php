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
    public $voucherCode = '';
    public $appliedVoucher = null;

    public function mount()
    {
        $this->loadCart();
        if ($this->cart && $this->cart->items) {
            $this->selectedItems = $this->cart->items->pluck('id')->map(fn($id) => (string)$id)->toArray();
        }
        $this->appliedVoucher = session('applied_voucher', null);
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

        if ($voucher->is_shipping_voucher) {
            session()->flash('voucher_error', 'Voucher ongkir hanya dapat digunakan di halaman Checkout.');
            return;
        }

        $cartQuantity = 0;
        if ($this->cart && $this->cart->items) {
            foreach ($this->cart->items as $item) {
                if (in_array((string)$item->id, $this->selectedItems)) {
                    $cartQuantity += $item->quantity;
                }
            }
        }
        
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

    public function removeVoucher()
    {
        $this->appliedVoucher = null;
        session()->forget('applied_voucher');
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
                    $price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;
                    $subtotal += $price * $item->quantity;
                }
            }
        }

        $resellerDiscount = 0;
        if (auth()->check() && auth()->user()->hasRole('reseller') && auth()->user()->reseller_status === 'active') {
            $discountPercent = \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 0;
            $resellerDiscount = $subtotal * ($discountPercent / 100);
        }

        $baseTotalForVoucher = max(0, $subtotal - $resellerDiscount);

        $discountAmount = 0;
        if ($this->appliedVoucher) {
            if ($this->appliedVoucher['min_purchase'] > 0 && $baseTotalForVoucher < $this->appliedVoucher['min_purchase']) {
                $this->removeVoucher();
                session()->flash('voucher_error', 'Total belanja tidak memenuhi syarat minimum voucher.');
            } else {
                if ($this->appliedVoucher['discount_type'] === 'fixed') {
                    $discountAmount = $this->appliedVoucher['discount_amount'];
                } else {
                    $discountAmount = $baseTotalForVoucher * ($this->appliedVoucher['discount_amount'] / 100);
                    if ($this->appliedVoucher['max_discount'] > 0 && $discountAmount > $this->appliedVoucher['max_discount']) {
                        $discountAmount = $this->appliedVoucher['max_discount'];
                    }
                }
            }
        }
        
        $grandTotal = max(0, $baseTotalForVoucher - $discountAmount);
        
        $availableVouchers = \App\Models\Voucher::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->get();

        return view('livewire.cart', [
            'subtotal' => $subtotal,
            'resellerDiscount' => $resellerDiscount,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
            'availableVouchers' => $availableVouchers,
        ]);
    }
}
