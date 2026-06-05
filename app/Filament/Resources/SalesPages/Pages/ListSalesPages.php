<?php

namespace App\Filament\Resources\SalesPages\Pages;

use App\Filament\Resources\SalesPages\SalesPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesPages extends ListRecords
{
    protected static string $resource = SalesPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
