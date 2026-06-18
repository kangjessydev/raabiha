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
    public $selectedOptions = [];
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
    public function availableAttributes()
    {
        if (!isset($this->product) || !$this->product->has_variants) return [];
        
        $attributes = [];
        foreach ($this->product->variants as $variant) {
            foreach ($variant->attributeOptions as $option) {
                $attrSlug = $option->attribute->slug;
                if (!isset($attributes[$attrSlug])) {
                    $attributes[$attrSlug] = [
                        'name' => $option->attribute->name,
                        'slug' => $attrSlug,
                        'type' => $option->attribute->type,
                        'options' => []
                    ];
                }
                if (!collect($attributes[$attrSlug]['options'])->contains('value', $option->value)) {
                    $attributes[$attrSlug]['options'][] = [
                        'id' => $option->id,
                        'value' => $option->value,
                        'meta' => $option->meta
                    ];
                }
            }
        }
        return $attributes;
    }

    public function isOptionAvailable($attrSlug, $optionValue)
    {
        if (!$this->product->has_variants) return true;

        $testSelection = $this->selectedOptions;
        $testSelection[$attrSlug] = $optionValue;
        $testSelection = array_filter($testSelection, fn($val) => $val !== '');

        foreach ($this->product->variants as $variant) {
            if ($variant->stock <= 0) continue;

            $matchesAll = true;
            foreach ($testSelection as $testAttrSlug => $testOptionVal) {
                $hasOption = $variant->attributeOptions->contains(function($opt) use ($testAttrSlug, $testOptionVal) {
                    return $opt->attribute->slug === $testAttrSlug && $opt->value === $testOptionVal;
                });
                if (!$hasOption) {
                    $matchesAll = false;
                    break;
                }
            }
            if ($matchesAll) return true;
        }
        return false;
    }

    public function selectOption($attrSlug, $optionValue)
    {
        if (!$this->isOptionAvailable($attrSlug, $optionValue)) return;
        
        if ($this->selectedOptions[$attrSlug] === $optionValue) {
            $this->selectedOptions[$attrSlug] = ''; // Deselect
        } else {
            $this->selectedOptions[$attrSlug] = $optionValue;
        }
    }

    public function getMatchedVariant()
    {
        if (!$this->product->has_variants) return null;
        
        return $this->product->variants->first(function($variant) {
            foreach ($this->selectedOptions as $slug => $val) {
                if (empty($val)) return false;
                $hasOption = $variant->attributeOptions->contains(function($opt) use ($slug, $val) {
                    return $opt->attribute->slug === $slug && $opt->value === $val;
                });
                if (!$hasOption) return false;
            }
            return true;
        });
    }

    #[Computed]
    public function currentPrice()
    {
        $variant = $this->getMatchedVariant();
        if ($variant) {
            return $variant->effective_price * $this->quantity;
        }
        return $this->product->effective_price * $this->quantity;
    }

    #[Computed]
    public function currentOriginalPrice()
    {
        $variant = $this->getMatchedVariant();
        if ($variant && $variant->is_price_override && $variant->price !== null) {
            return $variant->price * $this->quantity;
        }
        return $this->product->price * $this->quantity;
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

    #[Computed]
    public function ratingDistribution()
    {
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $totalReviews = $this->reviews->count();

        if ($totalReviews > 0) {
            foreach ($this->reviews as $review) {
                if (isset($distribution[$review->rating])) {
                    $distribution[$review->rating]++;
                }
            }
            foreach ($distribution as $stars => $count) {
                $distribution[$stars] = round(($count / $totalReviews) * 100);
            }
        } else {
            // Generate dummy distribution based on effective rating
            $rating = round($this->product->effective_rating * 2) / 2; 
            if ($rating >= 4.5) {
                $distribution[5] = 85;
                $distribution[4] = 15;
            } elseif ($rating >= 4.0) {
                $distribution[5] = 40;
                $distribution[4] = 50;
                $distribution[3] = 10;
            } elseif ($rating >= 3.0) {
                $distribution[4] = 30;
                $distribution[3] = 50;
                $distribution[2] = 20;
            } elseif ($rating > 0) {
                $distribution[ceil($rating)] = 100;
            }
        }

        return $distribution;
    }

    public function mount($slug = null)
    {
        \Log::info("ProductDetail mount called with slug: " . var_export($slug, true));
        $this->slug = $slug;
        try {
            $this->product = Product::with(['variants.attributeOptions.attribute'])->where('slug', $slug)->firstOrFail();
        } catch (\Exception $e) {
            \Log::error("ProductDetail mount failed: " . $e->getMessage());
            throw $e;
        }

        // Initialize selectedOptions with empty strings for all available attributes
        foreach ($this->availableAttributes() as $attrSlug => $data) {
            $this->selectedOptions[$attrSlug] = '';
        }
        
        // Resolve Curator Media URLs
        $this->galleryUrls = [];
        if (!empty($this->product->images) && is_array($this->product->images)) {
            // Check if it's new Curator ID format or old path format
            if (is_numeric($this->product->images[0])) {
                $mediaItems = \Awcodes\Curator\Models\Media::whereIn('id', $this->product->images)->get();
                // To maintain order
                foreach ($this->product->images as $id) {
                    $media = $mediaItems->firstWhere('id', $id);
                    if ($media && \Illuminate\Support\Facades\Storage::disk('public')->exists($media->path)) {
                        $this->galleryUrls[] = $media->url;
                    }
                }
            } else {
                // Fallback for old strings
                foreach ($this->product->images as $path) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        $this->galleryUrls[] = asset('storage/' . $path);
                    }
                }
            }
        }

        // If gallery is completely empty after validation, push a placeholder
        if (empty($this->galleryUrls)) {
            $this->galleryUrls[] = asset('assets/images/placeholder.png');
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
            foreach ($this->availableAttributes() as $attrSlug => $data) {
                if (empty($this->selectedOptions[$attrSlug])) {
                    session()->flash('error', 'Silakan lengkapi pilihan varian terlebih dahulu.');
                    return;
                }
            }
        }

        // 2. Find Variant and check stock
        $variantId = null;
        if ($this->product->has_variants) {
            $matchedVariant = $this->getMatchedVariant();

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
            'is_approved' => true,
        ]);

        $this->reset(['reviewRating', 'reviewComment', 'showReviewForm']);
        session()->flash('review_success', 'Terima kasih! Ulasan Anda berhasil ditambahkan.');
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

    #[Computed]
    public function relatedProducts()
    {
        return Product::where('is_active', true)
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();
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
