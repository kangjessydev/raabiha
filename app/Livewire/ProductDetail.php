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
        if (!isset($this->product)) return [];
        
        return \App\Models\AttributeOption::whereHas('productVariants', function($q) {
            $q->where('product_id', $this->product->id);
        })->whereHas('attribute', function($q) {
            $q->where('slug', 'ukuran');
        })->get();
    }

    #[Computed]
    public function colors()
    {
        if (!isset($this->product)) return [];
        
        return \App\Models\AttributeOption::whereHas('productVariants', function($q) {
            $q->where('product_id', $this->product->id);
        })->whereHas('attribute', function($q) {
            $q->where('slug', 'warna');
        })->get();
    }

    #[Computed]
    public function currentPrice()
    {
        $price = $this->product->price;

        if ($this->product->has_variants && $this->selectedSize && $this->selectedColor) {
            $matchedVariant = $this->product->variants->first(function($variant) {
                $hasSize = $variant->attributeOptions->contains(function($opt) {
                    return $opt->value === $this->selectedSize && $opt->attribute->slug === 'ukuran';
                });
                $hasColor = $variant->attributeOptions->contains(function($opt) {
                    return $opt->value === $this->selectedColor && $opt->attribute->slug === 'warna';
                });
                return $hasSize && $hasColor;
            });

            if ($matchedVariant && $matchedVariant->price !== null) {
                $price = $matchedVariant->price;
            }
        }

        return $price * $this->quantity;
    }

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->product = Product::with(['variants.attributeOptions.attribute'])->where('slug', $slug)->firstOrFail();
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
