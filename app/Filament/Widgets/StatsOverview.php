<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Omset Bulanan', 'Rp 45.850.000')
                ->description('Naik 12% dari bulan lalu')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total Pesanan Aktif', '1,245')
                ->description('34 pesanan butuh diproses')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
            Stat::make('Pengunjung Toko (Bulan Ini)', '14.2K')
                ->description('Tingkat Konversi 3.2%')
                ->descriptionIcon('heroicon-m-users')
                ->chart([2, 5, 4, 8, 5, 12, 10])
                ->color('primary'),
        ];
    }
}
