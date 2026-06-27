<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use App\Filament\Imports\VoucherImporter;
use App\Filament\Exports\VoucherExporter;
use Filament\Resources\Pages\ListRecords;

class ListVouchers extends ListRecords
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Impor Voucher')
                ->importer(VoucherImporter::class),

            ExportAction::make()
                ->label('Ekspor Voucher')
                ->exporter(VoucherExporter::class),
                
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Vouchers\Widgets\VoucherStatsWidget::class,
        ];
    }
}
