<?php

namespace App\Filament\Resources\Cashflows\Pages;

use App\Filament\Resources\Cashflows\CashflowResource;
use App\Filament\Resources\Cashflows\Widgets\CashflowStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashflows extends ListRecords
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('+ Catat Transaksi'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashflowStatsWidget::class,
        ];
    }
}
