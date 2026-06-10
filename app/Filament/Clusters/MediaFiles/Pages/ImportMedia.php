<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use App\Filament\Imports\OrderImporter;
use App\Filament\Imports\ProductImporter;
use BackedEnum;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;

class ImportMedia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected string $view = 'filament.clusters.media-files.pages.import-media';

    protected static ?string $cluster = MediaFiles::class;

    public static function getNavigationLabel(): string
    {
        return 'Impor Data';
    }

    public function getTitle(): string
    {
        return 'Impor Data';
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('import_products')
                ->label('Impor Produk')
                ->importer(ProductImporter::class)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            ImportAction::make('import_orders')
                ->label('Impor Pesanan')
                ->importer(OrderImporter::class)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success'),
        ];
    }
}
