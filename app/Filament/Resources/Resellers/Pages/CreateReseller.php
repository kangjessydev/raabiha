<?php

namespace App\Filament\Resources\Resellers\Pages;

use App\Filament\Resources\Resellers\ResellerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReseller extends CreateRecord
{
    protected static string $resource = ResellerResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
