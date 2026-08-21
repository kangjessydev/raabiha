<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosStampLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pos_customer_id',
        'order_id',
        'type',
        'stamps',
        'points',
        'description',
        'created_at',
    ];

    protected $casts = [
        'stamps'     => 'integer',
        'points'     => 'integer',
        'created_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(PosCustomer::class, 'pos_customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
