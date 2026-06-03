<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

use BackedEnum;

class MediaFiles extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Media Files';
    protected static ?string $slug = 'media-files';
}
