<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::updated(function ($order) {
            $order->orderRequests()
                ->where('type', 'change')
                ->where('status', 'approved')
                ->update(['status' => 'completed']);
        });
    }

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'source',
        'voucher_id',
        'applied_voucher_ids',
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
        'pos_session_id',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'cash_paid',
        'cash_change',
        'is_reserved',
        'pickup_date',
        'payment_details',
        'void_by_id',
        'void_reason',
        'void_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'applied_voucher_ids' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'is_reserved' => 'boolean',
        'pickup_date' => 'date',
        'void_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function posReturns(): HasMany
    {
        return $this->hasMany(PosReturn::class);
    }

    public function refundRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function orderRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderRequest::class);
    }

    public function getFormattedPaymentMethodAttribute(): string
    {
        $method = strtolower($this->payment_method ?? '');

        if ($method === 'split') {
            $details = is_string($this->payment_details) 
                ? json_decode($this->payment_details, true) 
                : ($this->payment_details ?? []);

            if (!empty($details['split_payments']) && is_array($details['split_payments'])) {
                $parts = [];
                foreach ($details['split_payments'] as $sp) {
                    $m = strtolower($sp['method'] ?? '');
                    if ($m === 'cash' || $m === 'tunai') {
                        $parts[] = 'TUNAI';
                    } else {
                        $parts[] = strtoupper($m);
                    }
                }
                if (!empty($parts)) {
                    return implode(' + ', array_unique($parts));
                }
            }
            return 'SPLIT PAYMENT';
        }

        if (in_array($method, ['cash', 'tunai'])) {
            return 'TUNAI';
        }

        return strtoupper($this->payment_method ?? 'TUNAI');
    }
}
