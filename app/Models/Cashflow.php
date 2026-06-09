<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cashflow extends Model
{
    protected $fillable = [
        'transaction_date',
        'type',
        'category',
        'amount',
        'description',
        'order_id',
        'source',
        'is_reversed',
        'reversal_note',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'is_reversed'      => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope: hanya entri yang aktif (belum di-reverse)
     */
    public function scopeActive($query)
    {
        return $query->where('is_reversed', false);
    }
}
