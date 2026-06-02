<?php

namespace App\Filament\Resources\Salespages\Pages;

use App\Filament\Resources\Salespages\SalespageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalespages extends ListRecords
{
    protected static string $resource = SalespageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
