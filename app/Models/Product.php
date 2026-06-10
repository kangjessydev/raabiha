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
        'description',
        'images',
        'price',
        'reseller_price',
        'stock',
        'weight',
        'has_variants',
        'meta_title',
        'meta_description',
        'wholesale_pricing',
        'promo_rules',
        'is_active',
        'minimum_stock',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_variants' => 'boolean',
        'wholesale_pricing' => 'array',
        'promo_rules' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getEffectivePriceAttribute()
    {
        $price = $this->price;
        
        if (auth()->check() && auth()->user()->hasRole('reseller')) {
            // Check if there is a specific reseller price for this product
            if ($this->reseller_price !== null && $this->reseller_price > 0) {
                return $this->reseller_price;
            }
            
            // Apply flat global discount
            $discountPercent = \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 20;
            return $price * (1 - ($discountPercent / 100));
        }

        return $price;
    }
}
