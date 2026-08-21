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
        'is_off_schedule',
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
        $query = static::where('status', 'open');

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        $activeSessions = $query->get();
        if ($activeSessions->isEmpty()) {
            return;
        }

        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        $restrictionEnabled = filter_var($settings['pos_shift_restriction_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $overtimeMaxHours = (int) ($settings['pos_shift_overtime_max_hours'] ?? 4);

        $rawMasterShifts = $settings['pos_master_shifts'] ?? [];
        $masterShifts = is_string($rawMasterShifts) ? (json_decode($rawMasterShifts, true) ?: []) : (is_array($rawMasterShifts) ? $rawMasterShifts : []);

        $rawWhitelist = $settings['pos_whitelist_users'] ?? [];
        $whitelistUsers = is_string($rawWhitelist) ? (json_decode($rawWhitelist, true) ?: []) : (is_array($rawWhitelist) ? $rawWhitelist : []);

        foreach ($activeSessions as $session) {
            $shouldClose = false;
            $closeReason = '';

            // 1. Cek Safety Net Subuh (shift dari hari kemarin atau sebelumnya)
            if ($session->opened_at < now()->startOfDay()) {
                $shouldClose = true;
                $closeReason = 'Otomatis ditutup oleh sistem (Safety Net Subuh - Kasir Lupa Tutup Shift)';
            }

            // 2. Cek apakah melebihi Batas Lembur Max Shift yang terikat
            if (!$shouldClose && $restrictionEnabled) {
                $userShift = static::findAssignedShiftForUser($session->cashier_id, $masterShifts, $whitelistUsers);

                if ($userShift && !empty($userShift['end_time'])) {
                    $openedDateStr = $session->opened_at->format('Y-m-d');
                    $shiftEndTime = \Carbon\Carbon::parse($openedDateStr . ' ' . $userShift['end_time']);

                    if (!empty($userShift['start_time'])) {
                        $shiftStartTime = \Carbon\Carbon::parse($openedDateStr . ' ' . $userShift['start_time']);
                        if ($shiftEndTime->lt($shiftStartTime)) {
                            $shiftEndTime->addDay();
                        }
                    }

                    $cutoffTime = $shiftEndTime->copy()->addHours($overtimeMaxHours);

                    if (now()->gt($cutoffTime)) {
                        $shouldClose = true;
                        $closeReason = "Otomatis ditutup oleh sistem (Batas Jam Kerja & Lembur Max {$overtimeMaxHours} Jam Terlampaui)";
                    }
                }
            }

            if ($shouldClose) {
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
                    'closed_at'            => now(),
                    'expected_ending_cash' => $expected,
                    'actual_ending_cash'   => $expected,
                    'difference_cash'      => 0,
                    'notes'                => $closeReason,
                ]);
            }
        }
    }

    public static function findAssignedShiftForUser(int $userId, array $masterShifts, array $whitelistUsers): ?array
    {
        // 1. Cek dari whitelist terlebih dahulu
        foreach ($whitelistUsers as $w) {
            if (isset($w['user_id']) && (string)$w['user_id'] === (string)$userId) {
                if (!empty($w['shift_name'])) {
                    foreach ($masterShifts as $s) {
                        if (($s['shift_name'] ?? '') === $w['shift_name']) {
                            return $s;
                        }
                    }
                }
                // Jika di whitelist tapi shift_name kosong -> Bebas (tanpa batasan shift)
                return null;
            }
        }

        // 2. Cek dari master shifts assigned_cashiers (role kasir)
        foreach ($masterShifts as $s) {
            $assigned = $s['assigned_cashiers'] ?? [];
            if (is_array($assigned) && in_array((string)$userId, array_map('strval', $assigned), true)) {
                return $s;
            }
        }

        return null;
    }
}
