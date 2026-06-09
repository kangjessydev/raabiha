<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\SalesTrendChart;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $cluster = \App\Filament\Clusters\Dashboard\DashboardCluster::class;
    
    protected static string $routePath = 'ringkasan';
    
    protected static ?string $navigationLabel = 'Ringkasan';
    
    protected static ?string $title = 'Ringkasan Dasbor';
    
    public static function getRouteName(?\Filament\Panel $panel = null): string
    {
        return 'filament.' . ($panel ? $panel->getId() : filament()->getCurrentPanel()->getId()) . '.dashboard.pages.dashboard';
    }

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            SalesTrendChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}

