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
    public $galleryUrls = [];

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
        
        // Resolve Curator Media URLs
        $this->galleryUrls = [];
        if (!empty($this->product->images) && is_array($this->product->images)) {
            // Check if it's new Curator ID format or old path format
            if (is_numeric($this->product->images[0])) {
                $mediaItems = \Awcodes\Curator\Models\Media::whereIn('id', $this->product->images)->get();
                // To maintain order
                foreach ($this->product->images as $id) {
                    $media = $mediaItems->firstWhere('id', $id);
                    if ($media) {
                        $this->galleryUrls[] = $media->url;
                    }
                }
            } else {
                // Fallback for old strings
                foreach ($this->product->images as $path) {
                    $this->galleryUrls[] = asset('storage/' . $path);
                }
            }
        }
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
        // 1. Validation
        if ($this->product->has_variants) {
            if (empty($this->selectedSize) || empty($this->selectedColor)) {
                session()->flash('error', 'Silakan pilih ukuran dan warna terlebih dahulu.');
                return;
            }
        }

        // 2. Find Variant and check stock
        $variantId = null;
        if ($this->product->has_variants) {
            $matchedVariant = $this->product->variants->first(function($variant) {
                $hasSize = $variant->attributeOptions->contains(function($opt) {
                    return $opt->value === $this->selectedSize && $opt->attribute->slug === 'ukuran';
                });
                $hasColor = $variant->attributeOptions->contains(function($opt) {
                    return $opt->value === $this->selectedColor && $opt->attribute->slug === 'warna';
                });
                return $hasSize && $hasColor;
            });

            if (!$matchedVariant) {
                session()->flash('error', 'Kombinasi varian tidak ditemukan atau habis.');
                return;
            }
            $variantId = $matchedVariant->id;
            
            if ($matchedVariant->stock < $this->quantity) {
                session()->flash('error', 'Stok varian tidak mencukupi.');
                return;
            }
        } else {
            if ($this->product->stock < $this->quantity) {
                session()->flash('error', 'Stok produk tidak mencukupi.');
                return;
            }
        }

        // 3. Get or Create Cart
        if (auth()->check()) {
            $cart = \App\Models\Cart::firstOrCreate(['user_id' => auth()->id(), 'is_buy_now' => false]);
        } else {
            // Ensure session is started and persisted
            session()->put('cart_active', true);
            $cart = \App\Models\Cart::firstOrCreate(['session_id' => session()->getId(), 'user_id' => null, 'is_buy_now' => false]);
        }

        // 4. Add or Update Item in Cart
        $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $this->product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $this->quantity;
            $cartItem->save();
        } else {
            \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $this->product->id,
                'product_variant_id' => $variantId,
                'quantity' => $this->quantity,
            ]);
        }

        $this->dispatch('cart-updated');
        
        session()->flash('message', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
