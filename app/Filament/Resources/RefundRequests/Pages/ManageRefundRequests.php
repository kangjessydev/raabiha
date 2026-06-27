<?php

namespace App\Filament\Resources\RefundRequests\Pages;

use App\Filament\Resources\RefundRequests\RefundRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRefundRequests extends ManageRecords
{
    protected static string $resource = RefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\RefundRequests\Widgets\RefundStatsWidget::class,
        ];
    }
}
