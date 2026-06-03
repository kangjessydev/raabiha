<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use Filament\Pages\Page;

class ImportMedia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.clusters.media-files.pages.import-media';

    protected static ?string $cluster = MediaFiles::class;
    
    protected static ?string $title = 'Import Media';
}
