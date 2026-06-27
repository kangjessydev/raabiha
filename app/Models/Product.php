<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'images',
        'price',
        'discount_price',
        'reseller_price',
        'stock',
        'weight',
        'has_variants',
        'meta_title',
        'meta_description',
        'wholesale_pricing',
        'promo_rules',
        'is_active',
        'is_hidden',
        'minimum_stock',
        'purchase_price',
        'rating',
        'sold_count',
        'has_free_shipping',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
        'has_variants' => 'boolean',
        'has_free_shipping' => 'boolean',
        'wholesale_pricing' => 'array',
        'promo_rules' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'sold_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getSellingPriceAttribute()
    {
        return ($this->discount_price !== null && $this->discount_price > 0) ? $this->discount_price : $this->price;
    }

    public function getEffectivePriceAttribute()
    {
        $price = $this->selling_price;
        
        if (auth()->check() && auth()->user()->hasRole('reseller')) {
            // Check if there is a specific reseller price for this product
            if ($this->reseller_price !== null && $this->reseller_price > 0) {
                return $this->reseller_price;
            }
            
            // Apply flat global discount
            $discountPercent = \Illuminate\Support\Facades\Cache::remember('reseller_discount_percent', 86400, function () {
                return \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 20;
            });
            return $price * (1 - ($discountPercent / 100));
        }

        return $price;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getEffectiveRatingAttribute()
    {
        // Get actual average rating from reviews (cached or computed)
        // For efficiency in a catalog, this is usually loaded via withAvg() in queries.
        // We'll fall back to calculating it if not loaded.
        $actualRating = $this->reviews_avg_rating ?? $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
        
        // Return the higher value between actual and manual dummy rating
        return max(round($actualRating, 1), $this->rating);
    }

    public function getEffectiveSoldCountAttribute()
    {
        // Get actual sold count from order items (usually aggregated or counted via observers)
        // For now, we will assume a generic relation or field. 
        // We'll just rely on the manual `sold_count` override compared to a potential `actual_sold` column if we add one later, 
        // or just return the manual one since actual sales counting requires joining OrderItem.
        // Let's assume actual_sold doesn't exist yet, we just return the manual one or sum of order items.
        
        $actualSold = $this->order_items_sum_quantity ?? \App\Models\OrderItem::where('product_id', $this->id)
            ->whereHas('order', function($q) {
                $q->where('payment_status', 'PAID');
            })->sum('quantity') ?? 0;

        return max($actualSold, $this->sold_count);
    }

    public function getIsOutOfStockAttribute(): bool
    {
        if ($this->has_variants) {
            return $this->variants->sum('stock') <= 0;
        }
        return $this->stock <= 0;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->has_variants) {
            return $this->variants->sum('stock');
        }
        return $this->stock;
    }
}
