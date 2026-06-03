<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use Filament\Pages\Page;

class ExportMedia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.clusters.media-files.pages.export-media';

    protected static ?string $cluster = MediaFiles::class;
    
    protected static ?string $title = 'Export Media';
}
