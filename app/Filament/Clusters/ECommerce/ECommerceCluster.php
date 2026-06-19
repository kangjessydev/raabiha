<?php

namespace App\Filament\Clusters\ECommerce;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ECommerceCluster extends Cluster
{
    public static function getNavigationIcon(): ?string { return 'heroicon-o-shopping-bag'; }
    protected static ?string $navigationLabel = 'E-Commerce';
    protected static ?string $clusterBreadcrumb = 'E-Commerce';
    protected static ?int $navigationSort = 1;
}
