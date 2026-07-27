<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'order_id',
        'pos_session_id',
        'cashier_id',
        'supervisor_id',
        'type',
        'reason',
        'returned_subtotal',
        'exchanged_subtotal',
        'net_amount',
        'refund_payment_method',
        'refund_bank_name',
        'refund_bank_account',
    ];

    protected $casts = [
        'returned_subtotal' => 'decimal:2',
        'exchanged_subtotal' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosReturnItem::class);
    }

    public function returnedItems(): HasMany
    {
        return $this->hasMany(PosReturnItem::class)->where('type', 'returned');
    }

    public function exchangedItems(): HasMany
    {
        return $this->hasMany(PosReturnItem::class)->where('type', 'exchanged');
    }
}
