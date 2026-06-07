<?php

namespace App\Filament\Clusters\Dashboard;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class DashboardCluster extends Cluster
{
    public static function getNavigationIcon(): ?string { return 'heroicon-o-home'; }
    protected static ?string $navigationLabel = 'Dasbor';
    protected static ?string $clusterBreadcrumb = 'Dasbor';
    protected static ?int $navigationSort = 1;
}
