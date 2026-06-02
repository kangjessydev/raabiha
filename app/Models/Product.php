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
        'wholesale_pricing',
        'promo_rules',
        'is_active',
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
}
