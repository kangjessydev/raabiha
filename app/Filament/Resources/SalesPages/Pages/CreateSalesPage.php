<?php

namespace App\Filament\Resources\SalesPages\Pages;

use App\Filament\Resources\SalesPages\SalesPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesPage extends CreateRecord
{
    protected static string $resource = SalesPageResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
