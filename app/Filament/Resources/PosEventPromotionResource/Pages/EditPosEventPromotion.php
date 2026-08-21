<?php

namespace App\Filament\Resources\PosEventPromotionResource\Pages;

use App\Filament\Resources\PosEventPromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosEventPromotion extends EditRecord
{
    protected static string $resource = PosEventPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
