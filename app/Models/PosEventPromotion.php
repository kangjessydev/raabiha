<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosEventPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_amount',
        'applies_to',
        'included_product_ids',
        'excluded_product_ids',
        'included_category_ids',
        'excluded_category_ids',
        'min_purchase',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'included_product_ids' => 'array',
        'excluded_product_ids' => 'array',
        'included_category_ids' => 'array',
        'excluded_category_ids' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Determine if a given product (and its category_id) qualifies for this event promotion.
     */
    public function isProductEligible(int $productId, ?int $categoryId = null): bool
    {
        // 1. Check exclusions first
        if (!empty($this->excluded_product_ids) && in_array($productId, $this->excluded_product_ids)) {
            return false;
        }

        if ($categoryId && !empty($this->excluded_category_ids) && in_array($categoryId, $this->excluded_category_ids)) {
            return false;
        }

        // 2. All items case
        if ($this->applies_to === 'all_items') {
            return true;
        }

        // 3. Specific products case
        if ($this->applies_to === 'specific_products') {
            return !empty($this->included_product_ids) && in_array($productId, $this->included_product_ids);
        }

        // 4. Specific categories case
        if ($this->applies_to === 'specific_categories') {
            return $categoryId && !empty($this->included_category_ids) && in_array($categoryId, $this->included_category_ids);
        }

        return false;
    }
}
