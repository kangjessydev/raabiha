<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderRequests extends ManageRecords
{
    protected static string $resource = OrderRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
