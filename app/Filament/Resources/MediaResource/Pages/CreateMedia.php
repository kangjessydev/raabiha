<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\CreateMedia as BaseCreateMedia;

class CreateMedia extends BaseCreateMedia
{
    protected static string $resource = MediaResource::class;
}
