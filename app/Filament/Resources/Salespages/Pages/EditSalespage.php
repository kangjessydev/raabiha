<?php

namespace App\Filament\Resources\Salespages\Pages;

use App\Filament\Resources\Salespages\SalespageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalespage extends EditRecord
{
    protected static string $resource = SalespageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
