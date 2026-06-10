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
        'name',
        'sku',
        'is_price_override',
        'price',
        'reseller_price',
        'is_weight_override',
        'weight',
        'stock',
        'is_active',
        'minimum_stock',
    ];

    protected $casts = [
        'is_price_override' => 'boolean',
        'is_weight_override' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttributeOptionProductVariant::class, 'product_variant_id');
    }

    public function attributeOptions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'attribute_option_product_variant');
    }

    public function getEffectivePriceAttribute()
    {
        $basePrice = $this->is_price_override && $this->price !== null ? $this->price : $this->product->price;
        
        if (auth()->check() && auth()->user()->hasRole('reseller')) {
            if ($this->is_price_override && $this->reseller_price !== null && $this->reseller_price > 0) {
                return $this->reseller_price;
            }
            if (!$this->is_price_override && $this->product->reseller_price !== null && $this->product->reseller_price > 0) {
                return $this->product->reseller_price;
            }
            
            $discountPercent = \App\Models\SiteSetting::where('key', 'reseller_discount_percent')->value('value') ?? 20;
            return $basePrice * (1 - ($discountPercent / 100));
        }

        return $basePrice;
    }
}
