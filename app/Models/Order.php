<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_cost',
        'discount_total',
        'grand_total',
        'payment_method',
        'payment_status',
        'payment_id',
        'payment_url',
        'shipping_address',
        'courier',
        'awb_number',
        'notes',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
