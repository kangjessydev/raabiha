<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use Filament\Pages\Page;

use BackedEnum;

class ExportMedia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.clusters.media-files.pages.export-media';

    protected static ?string $cluster = MediaFiles::class;
    
    public static function getNavigationLabel(): string
    {
        return 'Export Media';
    }
}
