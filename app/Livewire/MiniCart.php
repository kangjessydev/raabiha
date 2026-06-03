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
    
    #[On('open-mini-cart')]
    public function open()
    {
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function removeItem($itemId)
    {
        $item = \App\Models\CartItem::find($itemId);
        if ($item) {
            $item->delete();
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $subtotal = 0;
        $count = 0;
        if ($this->cart && $this->cart->items) {
            foreach ($this->cart->items as $item) {
                $price = $item->variant && $item->variant->price ? $item->variant->price : $item->product->price;
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
