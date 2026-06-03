<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\EditMedia as BaseEditMedia;

class EditMedia extends BaseEditMedia
{
    protected static string $resource = MediaResource::class;
}
