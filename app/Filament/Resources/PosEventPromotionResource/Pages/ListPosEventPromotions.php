<?php

namespace App\Filament\Resources\PosEventPromotionResource\Pages;

use App\Filament\Resources\PosEventPromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosEventPromotions extends ListRecords
{
    protected static string $resource = PosEventPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
