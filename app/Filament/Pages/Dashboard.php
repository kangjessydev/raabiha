<?php

namespace App\Filament\Pages;

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
}
