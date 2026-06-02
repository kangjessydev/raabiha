<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Computed;

use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ProductDetail extends Component
{
    public $slug;
    public $product;
    public $selectedSize = '';
    public $selectedColor = '';
    public $quantity = 1;

    #[Computed]
    public function sizes()
    {
        // Dummy sizes for UI testing if no variants exist
        return ['XS/S', 'M/L', 'Oversized'];
    }

    #[Computed]
    public function colors()
    {
        // Dummy colors for UI testing
        return ['Charcoal', 'Slate Sand', 'Dusty Rose'];
    }

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->product = Product::with(['variants'])->where('slug', $slug)->firstOrFail();
    }

    public function incrementQuantity()
    {
        if ($this->quantity < 10) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        // To be implemented: actual cart logic (e.g. session, db)
        // For now, we will dispatch an event that can be caught by a Cart header component
        $this->dispatch('cart-updated');
        
        session()->flash('message', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
