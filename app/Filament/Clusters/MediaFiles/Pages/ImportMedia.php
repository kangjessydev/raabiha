<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use Filament\Pages\Page;

use BackedEnum;

class ImportMedia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected string $view = 'filament.clusters.media-files.pages.import-media';

    protected static ?string $cluster = MediaFiles::class;
    
    public static function getNavigationLabel(): string
    {
        return 'Import Media';
    }
}
