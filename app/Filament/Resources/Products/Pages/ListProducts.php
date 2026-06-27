<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Imports\ProductImporter;
use App\Filament\Exports\ProductExporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Impor Produk')
                ->importer(ProductImporter::class),

            ExportAction::make()
                ->label('Ekspor Produk')
                ->exporter(ProductExporter::class),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Products\Widgets\ProductStatsWidget::class,
        ];
    }
}
