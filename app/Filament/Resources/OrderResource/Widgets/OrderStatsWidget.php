<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $totalOrdersToday = Order::whereDate('created_at', $today)->count();
        $revenueToday = Order::whereDate('created_at', $today)->where('payment_status', 'PAID')->sum('grand_total');
        $pendingOrders = Order::where('status', 'PENDING')->count();

        return [
            Stat::make('Pesanan Baru (Hari Ini)', $totalOrdersToday)
                ->description('Total transaksi masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([2, 5, 3, 7, 4, 10, $totalOrdersToday])
                ->color('info')
                ->icon('heroicon-o-shopping-cart')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 dark:from-blue-950/50 dark:to-blue-900/50 rounded-xl',
                ]),
            Stat::make('Pendapatan (Hari Ini)', 'Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->description('Dari pesanan berstatus Lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([100000, 250000, 150000, 400000, 200000, 500000, $revenueToday])
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50/50 to-emerald-100/50 dark:from-emerald-950/50 dark:to-emerald-900/50 rounded-xl',
                ]),
            Stat::make('Menunggu Pembayaran', $pendingOrders)
                ->description('Pesanan berstatus Pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-amber-50/50 to-amber-100/50 dark:from-amber-950/50 dark:to-amber-900/50 rounded-xl',
                ]),
        ];
    }
}
