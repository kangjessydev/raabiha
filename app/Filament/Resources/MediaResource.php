<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\MediaFiles;
use Awcodes\Curator\Resources\Media\MediaResource as CuratorMediaResource;

use App\Filament\Resources\MediaResource\Pages\CreateMedia;
use App\Filament\Resources\MediaResource\Pages\EditMedia;
use App\Filament\Resources\MediaResource\Pages\ListMedia;

class MediaResource extends CuratorMediaResource
{
    protected static ?string $cluster = MediaFiles::class;
    
    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
