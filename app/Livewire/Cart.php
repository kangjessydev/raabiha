<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Cart extends Component
{
    public $cart;

    public function mount()
    {
        $this->loadCart();
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
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $subtotal = 0;
        if ($this->cart && $this->cart->items) {
            foreach ($this->cart->items as $item) {
                $price = $item->variant && $item->variant->price ? $item->variant->price : $item->product->price;
                $subtotal += $price * $item->quantity;
            }
        }

        return view('livewire.cart', [
            'subtotal' => $subtotal,
        ]);
    }
}
