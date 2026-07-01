<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'media_id',
        'name',
        'sku',
        'is_price_override',
        'price',
        'discount_price',
        'reseller_price',
        'is_weight_override',
        'weight',
        'stock',
        'is_active',
        'minimum_stock',
        'purchase_price',
    ];

    protected $casts = [
        'is_price_override' => 'boolean',
        'is_weight_override' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($variant) {
            $rawPrice = $variant->getAttributes()['price'] ?? null;
            $rawDiscount = $variant->getAttributes()['discount_price'] ?? null;
            $variant->is_price_override = ($rawPrice !== null && $rawPrice > 0) || ($rawDiscount !== null && $rawDiscount > 0);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(\Awcodes\Curator\Models\Media::class, 'media_id');
    }

    public function variantOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttributeOptionProductVariant::class, 'product_variant_id');
    }

    public function attributeOptions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'attribute_option_product_variant');
    }

    // Getters that fallback to parent product if empty
    public function getPriceAttribute($value)
    {
        if ($value !== null && $value > 0) {
            return $value;
        }
        return $this->product ? $this->product->price : null;
    }

    public function getPurchasePriceAttribute($value)
    {
        if ($value !== null && $value > 0) {
            return $value;
        }
        return $this->product ? $this->product->purchase_price : null;
    }

    public function getResellerPriceAttribute($value)
    {
        if ($value !== null && $value > 0) {
            return $value;
        }
        return $this->product ? $this->product->reseller_price : null;
    }

    public function getDiscountPriceAttribute($value)
    {
        return ($value !== null && $value > 0) ? $value : null;
    }

    public function getSellingPriceAttribute()
    {
        if ($this->discount_price !== null && $this->discount_price > 0) {
            return $this->discount_price;
        }
        return $this->price;
    }

    public function getEffectivePriceAttribute()
    {
        $basePrice = $this->selling_price;
        
        if (auth()->check() && auth()->user()->hasRole('reseller')) {
            if ($this->reseller_price !== null && $this->reseller_price > 0) {
                return $this->reseller_price;
            }
            
            $discountPercent = \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 20;
            return $basePrice * (1 - ($discountPercent / 100));
        }

        return $basePrice;
    }
}
