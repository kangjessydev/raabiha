<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Computed;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;

#[Layout('components.layouts.app')]
#[Lazy]
class ProductDetail extends Component
{
    public $slug;
    public $product;
    public $selectedSize = '';
    public $selectedColor = '';
    public $quantity = 1;
    public $galleryUrls = [];
    public $bsOpen = false;
    public $bsMode = 'cart';
    
    // Review Properties
    public $reviewRating = 0;
    public $reviewName = '';
    public $reviewComment = '';
    public $showReviewForm = false;

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

            if ($matchedVariant) {
                return $matchedVariant->effective_price * $this->quantity;
            }
        }

        return $this->product->effective_price * $this->quantity;
    }

    #[Computed]
    public function currentOriginalPrice()
    {
        $originalPrice = $this->product->price;

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

            if ($matchedVariant && $matchedVariant->is_price_override && $matchedVariant->price !== null) {
                $originalPrice = $matchedVariant->price;
            }
        }

        return $originalPrice * $this->quantity;
    }

    #[Computed]
    public function canReview()
    {
        if (!auth()->check()) return false;
        
        return \App\Models\Order::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereHas('items', function($q) {
                $q->where('product_id', $this->product->id);
            })->exists();
    }

    #[Computed]
    public function hasReviewed()
    {
        if (!auth()->check()) return false;
        return \App\Models\ProductReview::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->exists();
    }

    #[Computed]
    public function reviews()
    {
        if (!isset($this->product)) return collect();
        return \App\Models\ProductReview::where('product_id', $this->product->id)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function averageRating()
    {
        return $this->reviews->avg('rating') ?? 0;
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
        
        if (auth()->check()) {
            $this->reviewName = auth()->user()->name;
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
        session()->flash('success', 'Produk berhasil ditambahkan ke keranjang.');
        $this->bsOpen = false;
        
        // Let's also dispatch an event to trigger the flying animation
        // and optionally open the mini cart right after.
        $this->dispatch('product-added-to-cart');
    }

    public function buyNow()
    {
        // Add to cart first
        $this->addToCart();
        
        // If there's an error (e.g. out of stock, no variant selected), stop
        if (session()->has('error')) {
            return;
        }
        
        // Mark as buy_now or just redirect to checkout since we just added it to the cart
        return redirect()->to('/checkout');
    }

    public function submitReview()
    {
        if (!auth()->check()) {
            session()->flash('review_error', 'Anda harus login untuk memberikan ulasan.');
            return;
        }

        if (!$this->canReview) {
            session()->flash('review_error', 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan pesanan selesai.');
            return;
        }

        if ($this->hasReviewed) {
            session()->flash('review_error', 'Anda sudah memberikan ulasan untuk produk ini.');
            return;
        }

        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewName' => 'required|string|max:255',
            'reviewComment' => 'required|string|min:10',
        ]);

        \App\Models\ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => auth()->id(),
            'customer_name' => $this->reviewName,
            'customer_email' => auth()->user()->email,
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
            'is_approved' => false,
        ]);

        $this->reset(['reviewRating', 'reviewComment', 'showReviewForm']);
        session()->flash('review_success', 'Terima kasih! Ulasan Anda telah dikirim dan sedang menunggu persetujuan.');
    }

    public function render()
    {
        $title = $this->product->meta_title ?? $this->product->name;
        $description = $this->product->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($this->product->description), 160);
        $image = !empty($this->galleryUrls) ? $this->galleryUrls[0] : null;

        return view('livewire.product-detail')
            ->layout('components.layouts.app', [
                'title' => $title,
                'description' => $description,
                'image' => $image
            ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-[1440px] mx-auto px-0 md:px-[64px] pb-32 animate-pulse">
            <div class="grid grid-cols-1 md:grid-cols-[1.2fr_1fr] lg:grid-cols-[1.5fr_1fr] gap-8 md:gap-16 items-start">
                <div class="w-full h-[60vh] md:h-screen max-h-[800px] bg-[#e5e2de]"></div>
                <div class="px-6 md:px-0 py-8 sticky top-24">
                    <div class="h-10 bg-[#e5e2de] w-3/4 mb-4"></div>
                    <div class="h-6 bg-[#e5e2de] w-1/4 mb-8"></div>
                    <div class="h-4 bg-[#e5e2de] w-full mb-2"></div>
                    <div class="h-4 bg-[#e5e2de] w-full mb-2"></div>
                    <div class="h-4 bg-[#e5e2de] w-3/4 mb-12"></div>
                    <div class="h-12 bg-[#e5e2de] w-full mb-4"></div>
                    <div class="h-12 bg-[#e5e2de] w-full"></div>
                </div>
            </div>
        </div>
        HTML;
    }
}
