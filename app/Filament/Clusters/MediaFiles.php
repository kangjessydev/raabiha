<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class MediaFiles extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Media Files';
    protected static ?string $slug = 'media-files';
}
