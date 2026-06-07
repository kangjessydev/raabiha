<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_amount',
        'is_shipping_voucher',
        'min_purchase',
        'min_items',
        'max_discount',
        'specific_users',
        'exclude_resellers',
        'is_stackable',
        'free_gift_product_id',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'specific_users' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_shipping_voucher' => 'boolean',
        'exclude_resellers' => 'boolean',
        'is_stackable' => 'boolean',
        'discount_amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function freeGiftProduct()
    {
        return $this->belongsTo(Product::class, 'free_gift_product_id');
    }
}
