<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\ListMedia as BaseListMedia;

class ListMedia extends BaseListMedia
{
    protected static string $resource = MediaResource::class;
}
