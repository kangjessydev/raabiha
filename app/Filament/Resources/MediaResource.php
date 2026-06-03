<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\MediaFiles;
use Awcodes\Curator\Resources\Media\MediaResource as CuratorMediaResource;

class MediaResource extends CuratorMediaResource
{
    protected static ?string $cluster = MediaFiles::class;
}
