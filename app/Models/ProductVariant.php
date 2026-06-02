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
}
