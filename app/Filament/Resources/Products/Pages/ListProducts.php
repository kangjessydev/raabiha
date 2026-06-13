<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\ProductExporter::class),
            \Filament\Actions\ImportAction::make('import_raabiha')
                ->label('Import (Format Asli)')
                ->importer(\App\Filament\Imports\ProductImporter::class),
            \Filament\Actions\ImportAction::make('import_client')
                ->label('Import dari Klien (Gabungan)')
                ->importer(\App\Filament\Imports\ClientProductImporter::class)
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray'),
            CreateAction::make(),
        ];
    }
}
