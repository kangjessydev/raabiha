<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class CartBadge extends Component
{
    public $count = 0;

    public function mount()
    {
        $this->loadCart();
    }

    #[On('cart-updated')]
    public function loadCart()
    {
        if (auth()->check()) {
            $cart = \App\Models\Cart::with('items')
                ->where('user_id', auth()->id())
                ->where('is_buy_now', false)
                ->first();
        } else {
            $cart = \App\Models\Cart::with('items')
                ->where('session_id', session()->getId())
                ->whereNull('user_id')
                ->where('is_buy_now', false)
                ->first();
        }

        $count = 0;
        if ($cart && $cart->items) {
            foreach ($cart->items as $item) {
                $count += $item->quantity;
            }
        }
        $this->count = $count;
    }

    public function render()
    {
        return view('livewire.cart-badge');
    }
}
