<?php

namespace App\Filament\Resources\Cashflows\Widgets;

use App\Models\Cashflow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CashflowStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $totalIn = Cashflow::query()
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalOut = Cashflow::query()
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $balance = $totalIn - $totalOut;

        $totalInAll  = Cashflow::where('type', 'in')->sum('amount');
        $totalOutAll = Cashflow::where('type', 'out')->sum('amount');
        $balanceAll  = $totalInAll - $totalOutAll;

        return [
            Stat::make('💰 Pemasukan Bulan Ini', 'Rp ' . number_format($totalIn, 0, ',', '.'))
                ->description('Total Cash In ' . Carbon::now()->translatedFormat('F Y'))
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('💸 Pengeluaran Bulan Ini', 'Rp ' . number_format($totalOut, 0, ',', '.'))
                ->description('Total Cash Out ' . Carbon::now()->translatedFormat('F Y'))
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),

            Stat::make(
                $balance >= 0 ? '✅ Saldo Bersih Bulan Ini' : '⚠️ Defisit Bulan Ini',
                'Rp ' . number_format(abs($balance), 0, ',', '.')
            )
                ->description('Laba/Rugi kasar ' . Carbon::now()->translatedFormat('F Y'))
                ->color($balance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-scale'),

            Stat::make('📊 Saldo Keseluruhan', 'Rp ' . number_format(abs($balanceAll), 0, ',', '.'))
                ->description($balanceAll >= 0 ? 'Total akumulasi surplus' : 'Total akumulasi defisit')
                ->color($balanceAll >= 0 ? 'primary' : 'warning')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
