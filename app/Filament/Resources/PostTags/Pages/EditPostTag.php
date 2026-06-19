<?php

namespace App\Filament\Resources\PostTags\Pages;

use App\Filament\Resources\PostTags\PostTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostTag extends EditRecord
{
    protected static string $resource = PostTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
