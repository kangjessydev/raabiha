<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\CreateMedia as BaseCreateMedia;

class CreateMedia extends BaseCreateMedia
{
    protected static string $resource = MediaResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
