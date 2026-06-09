<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity_before'  => 'integer',
        'quantity_change'  => 'integer',
        'quantity_after'   => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
