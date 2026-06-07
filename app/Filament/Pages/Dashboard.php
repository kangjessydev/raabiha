<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $cluster = \App\Filament\Clusters\Dashboard\DashboardCluster::class;
    
    protected static ?string $navigationLabel = 'Ringkasan';
    
    protected static ?string $title = 'Ringkasan Dasbor';
}
