<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'expected_ending_cash',
        'actual_ending_cash',
        'difference_cash',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'expected_ending_cash' => 'decimal:2',
        'actual_ending_cash' => 'decimal:2',
        'difference_cash' => 'decimal:2',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'pos_session_id');
    }

    public static function autoCloseStaleSessions(?int $cashierId = null): void
    {
        $query = static::where('status', 'open')
            ->where('opened_at', '<', now()->startOfDay());

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        $staleSessions = $query->get();

        foreach ($staleSessions as $session) {
            $pettyCashIn = Cashflow::where('source', 'pos')
                ->where('category', 'pos_petty_cash')
                ->where('type', 'in')
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $pettyCashOut = Cashflow::where('source', 'pos')
                ->where('category', 'pos_petty_cash')
                ->where('type', 'out')
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $cashSales = $session->orders()
                ->whereNotIn('status', ['cancelled'])
                ->whereIn('payment_method', ['cash', 'tunai'])
                ->sum('grand_total');

            $expected = (float) $session->opening_cash + (float) $cashSales + (float) $pettyCashIn - (float) $pettyCashOut;

            $session->update([
                'status'               => 'closed',
                'closed_at'            => $session->opened_at->copy()->endOfDay(),
                'expected_ending_cash' => $expected,
                'actual_ending_cash'   => $expected,
                'difference_cash'      => 0,
                'notes'                => 'Otomatis ditutup oleh sistem (Safety Net Subuh - Kasir Lupa Tutup Shift)',
            ]);
        }
    }
}
