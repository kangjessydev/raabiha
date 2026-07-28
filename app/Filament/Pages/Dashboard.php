<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\SalesTrendChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\TopProductsWidget;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Dasbor Utama';
    protected static ?string $title = 'Dasbor Utama';

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            SalesTrendChart::class,
            RevenueChart::class,
            OrdersChart::class,
            TopProductsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}

