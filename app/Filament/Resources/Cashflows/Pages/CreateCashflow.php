<?php

namespace App\Filament\Resources\Cashflows\Pages;

use App\Filament\Resources\Cashflows\CashflowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashflow extends CreateRecord
{
    protected static string $resource = CashflowResource::class;

    /**
     * Otomatis set type=out dan source=manual sebelum disimpan.
     * Admin tidak perlu (dan tidak bisa) mengubah ini.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type']   = 'out';
        $data['source'] = 'manual';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
