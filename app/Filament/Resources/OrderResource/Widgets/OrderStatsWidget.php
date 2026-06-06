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
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('Pendapatan (Hari Ini)', 'Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->description('Dari pesanan berstatus Lunas')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Menunggu Pembayaran', $pendingOrders)
                ->description('Pesanan berstatus Pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
