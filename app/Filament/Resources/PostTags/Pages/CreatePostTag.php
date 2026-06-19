<?php

namespace App\Filament\Resources\PostTags\Pages;

use App\Filament\Resources\PostTags\PostTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostTag extends CreateRecord
{
    protected static string $resource = PostTagResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
