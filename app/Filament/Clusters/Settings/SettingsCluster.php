<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    public static function getNavigationIcon(): ?string { return 'heroicon-o-cog-8-tooth'; }
    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?string $clusterBreadcrumb = 'Pengaturan';
    protected static ?int $navigationSort = 3;

    }
