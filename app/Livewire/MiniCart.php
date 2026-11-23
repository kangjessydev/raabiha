<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class MiniCart extends Component
{
    public $cart;
    public $isOpen = false;

    public function mount()
    {
        $this->loadCart();
    }

    #[On('cart-updated')]
    public function loadCart()
    {
        if (auth()->check()) {
            $this->cart = \App\Models\Cart::with(['items.product', 'items.variant.media', 'items.variant.attributeOptions.attribute'])
                ->where('user_id', auth()->id())
                ->where('is_buy_now', false)
                ->first();
        } else {
            $this->cart = \App\Models\Cart::with(['items.product', 'items.variant.media', 'items.variant.attributeOptions.attribute'])
                ->where('session_id', session()->getId())
                ->whereNull('user_id')
                ->where('is_buy_now', false)
                ->first();
        }
    }
    
    #[On('open-mini-cart')]
    public function open()
    {
        $this->isOpen = true;
    }

    #[On('close-mini-cart')]
    public function close()
    {
        $this->isOpen = false;
    }

    public function incrementQuantity($itemId)
    {
        if (! $this->cart) {
            $this->loadCart();
        }

        $item = $this->cart ? $this->cart->items()->with(['product', 'variant'])->find($itemId) : null;
        if (! $item) {
            return;
        }

        if ($item->product_variant_id !== null) {
            if (! $item->variant || ! $item->variant->is_active) {
                return;
            }
            $availableStock = (int) $item->variant->stock;
        } else {
            if (! $item->product || ! $item->product->is_active) {
                return;
            }
            $availableStock = (int) ($item->product->stock ?? 0);
        }

        if ($item->quantity >= $availableStock) {
            return;
        }

        $item->increment('quantity');
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function decrementQuantity($itemId)
    {
        if (! $this->cart) {
            $this->loadCart();
        }

        $item = $this->cart ? $this->cart->items()->find($itemId) : null;
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($itemId)
    {
        if (! $this->cart) {
            $this->loadCart();
        }

        $item = $this->cart ? $this->cart->items()->find($itemId) : null;
        if ($item) {
            $item->delete();
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function proceedToCheckout()
    {
        session(['checkout_is_buy_now' => false]);
        session()->forget('checkout_item_ids');

        return redirect()->to('/checkout');
    }

    public function render()
    {
        $subtotal = 0;
        $count = 0;
        if ($this->cart && $this->cart->items) {
            foreach ($this->cart->items as $item) {
                $price = $item->variant ? $item->variant->effective_price : ($item->product ? $item->product->effective_price : 0);
                $subtotal += $price * $item->quantity;
                $count += $item->quantity;
            }
        }

        return view('livewire.mini-cart', [
            'subtotal' => $subtotal,
            'count' => $count
        ]);
    }
}
