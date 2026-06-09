<?php

namespace App\Filament\Resources\Cashflows\Widgets;

use App\Models\Cashflow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CashflowStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $month = Carbon::now()->format('Y-m');

        // Cache 5 menit — ringan di VPS Lite 2GB RAM
        $stats = Cache::remember("cashflow_stats_{$month}", 300, function () {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();

            // Single query untuk bulan ini menggunakan conditional aggregation
            $monthly = DB::table('cashflows')
                ->where('is_reversed', false)
                ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->selectRaw("
                    SUM(CASE WHEN type = 'in' AND source = 'order' THEN amount ELSE 0 END) as total_in,
                    SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as total_out
                ")
                ->first();

            // Single query untuk all-time
            $allTime = DB::table('cashflows')
                ->where('is_reversed', false)
                ->selectRaw("
                    SUM(CASE WHEN type = 'in' AND source = 'order' THEN amount ELSE 0 END) as total_in,
                    SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as total_out
                ")
                ->first();

            return [
                'monthly_in'    => (float) ($monthly->total_in ?? 0),
                'monthly_out'   => (float) ($monthly->total_out ?? 0),
                'alltime_in'    => (float) ($allTime->total_in ?? 0),
                'alltime_out'   => (float) ($allTime->total_out ?? 0),
            ];
        });

        $monthlyBalance = $stats['monthly_in'] - $stats['monthly_out'];
        $allTimeBalance = $stats['alltime_in'] - $stats['alltime_out'];
        $monthLabel     = Carbon::now()->translatedFormat('F Y');

        return [
            Stat::make('💰 Pemasukan Bulan Ini', 'Rp ' . number_format($stats['monthly_in'], 0, ',', '.'))
                ->description('Total penjualan lunas — ' . $monthLabel)
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('💸 Pengeluaran Bulan Ini', 'Rp ' . number_format($stats['monthly_out'], 0, ',', '.'))
                ->description('Total biaya operasional — ' . $monthLabel)
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),

            Stat::make(
                $monthlyBalance >= 0 ? '✅ Saldo Bersih Bulan Ini' : '⚠️ Defisit Bulan Ini',
                'Rp ' . number_format(abs($monthlyBalance), 0, ',', '.')
            )
                ->description('Laba/Rugi kasar ' . $monthLabel)
                ->color($monthlyBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-scale'),

            Stat::make(
                $allTimeBalance >= 0 ? '📊 Saldo Keseluruhan' : '📊 Defisit Keseluruhan',
                'Rp ' . number_format(abs($allTimeBalance), 0, ',', '.')
            )
                ->description($allTimeBalance >= 0 ? 'Total akumulasi surplus' : 'Total akumulasi defisit')
                ->color($allTimeBalance >= 0 ? 'primary' : 'warning')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
