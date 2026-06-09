<?php

namespace App\Filament\Resources\Cashflows\Pages;

use App\Filament\Resources\Cashflows\CashflowResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashflow extends EditRecord
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
