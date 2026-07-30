<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosCustomer extends Model
{
    protected $fillable = [
        'phone',
        'name',
        'stamp_count',
        'points_balance',
        'total_stamps_earned',
        'completed_cards_count',
        'total_visits',
        'total_spent',
        'last_visit_at',
    ];

    protected $casts = [
        'stamp_count'           => 'integer',
        'points_balance'        => 'integer',
        'total_stamps_earned'   => 'integer',
        'completed_cards_count' => 'integer',
        'total_visits'          => 'integer',
        'total_spent'           => 'decimal:2',
        'last_visit_at'         => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(PosStampLog::class, 'pos_customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_phone', 'phone');
    }

    public function debtPayments(): HasMany
    {
        return $this->hasMany(PosDebtPayment::class, 'pos_customer_id');
    }

    /**
     * Calculate total outstanding debt (unpaid kasbon orders).
     */
    public function getTotalDebtAttribute(): float
    {
        return (float) Order::where('customer_phone', $this->phone)
            ->where('payment_status', 'unpaid')
            ->where('is_kasbon', true)
            ->sum('due_amount');
    }

    /**
     * Helper: Normalisasi nomor HP ke format standar 08...
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleaned)) return null;

        if (str_starts_with($cleaned, '628')) {
            $cleaned = '08' . substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '0' . $cleaned;
        }

        return $cleaned;
    }
}
