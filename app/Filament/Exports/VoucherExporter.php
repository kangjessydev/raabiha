<?php

namespace App\Filament\Exports;

use App\Models\Voucher;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class VoucherExporter extends Exporter
{
    protected static ?string $model = Voucher::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Nama'),
            ExportColumn::make('code')->label('Kode'),
            ExportColumn::make('type')->label('Tipe Diskon'),
            ExportColumn::make('value')->label('Nilai Diskon'),
            ExportColumn::make('min_purchase')->label('Min. Belanja'),
            ExportColumn::make('max_discount')->label('Maks. Diskon'),
            ExportColumn::make('usage_limit')->label('Kuota'),
            ExportColumn::make('used_count')->label('Sudah Digunakan'),
            ExportColumn::make('is_active')->label('Aktif?'),
            ExportColumn::make('starts_at')->label('Mulai'),
            ExportColumn::make('ends_at')->label('Berakhir'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor voucher selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
