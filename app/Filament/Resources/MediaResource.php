<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\MediaFiles;
use Awcodes\Curator\Resources\Media\MediaResource as CuratorMediaResource;

class MediaResource extends CuratorMediaResource
{
    protected static ?string $cluster = MediaFiles::class;
    
    // We remove the default group so it uses the cluster
    protected static ?string $navigationGroup = null;
    
    // Maintain the icon
    protected static ?string $navigationIcon = 'heroicon-o-photo';
}
