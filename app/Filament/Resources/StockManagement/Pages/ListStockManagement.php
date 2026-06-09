<?php

namespace App\Filament\Resources\StockManagement\Pages;

use App\Filament\Resources\StockManagement\StockManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockManagement extends ListRecords
{
    protected static string $resource = StockManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada Create — halaman ini murni untuk edit stok cepat
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\StockManagement\Widgets\StockLogWidget::class,
        ];
    }
}
