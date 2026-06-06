<?php

namespace App\Filament\Resources\PromoBanners\Pages;

use App\Filament\Resources\PromoBanners\PromoBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePromoBanner extends CreateRecord
{
    protected static string $resource = PromoBannerResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
