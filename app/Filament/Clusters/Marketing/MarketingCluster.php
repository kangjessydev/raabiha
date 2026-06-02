<?php

namespace App\Filament\Clusters\Marketing;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class MarketingCluster extends Cluster
{
    public static function getNavigationIcon(): ?string { return 'heroicon-o-newspaper'; }
    protected static ?string $navigationLabel = 'Konten';
    protected static ?string $clusterBreadcrumb = 'Konten';
    protected static ?int $navigationSort = 2;

    }
