<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Computed;

class ProductDetail extends Component
{
    public $slug;
    public $product;
    public $selectedSize = '';
    public $selectedColor = '';
    public $quantity = 1;

    public function mount($slug)
    {
        $this->slug = $slug;
        
        // Let's find the product by slug. Since the db is configured,
        // we should query the Product model.
        // For now we will fetch the product eager loading variants and images if any.
        $this->product = Product::with(['variants.attributeOptions.attribute'])->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
