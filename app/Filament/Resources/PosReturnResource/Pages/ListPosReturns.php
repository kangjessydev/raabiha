<?php

namespace App\Filament\Resources\PosReturnResource\Pages;

use App\Filament\Resources\PosReturnResource;
use Filament\Resources\Pages\ListRecords;

class ListPosReturns extends ListRecords
{
    protected static string $resource = PosReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
