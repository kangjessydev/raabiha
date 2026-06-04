<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = Order::whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        $activeOrders = Order::whereIn('status', ['pending', 'paid', 'packed'])->count();
        $totalProducts = Product::where('is_active', true)->count();

        return [
            Stat::make('Total Pendapatan (Bulan Ini)', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description('Dari pesanan berstatus Lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Pesanan Aktif', $activeOrders)
                ->description('Pesanan yang butuh diproses')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
            Stat::make('Total Produk Aktif', $totalProducts)
                ->description('Produk yang tayang di katalog')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
        ];
    }
}
