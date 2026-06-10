<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Nama'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('No. HP'),
            ExportColumn::make('is_reseller')->label('Reseller?'),
            ExportColumn::make('reseller_status')->label('Status Reseller'),
            ExportColumn::make('created_at')->label('Tanggal Daftar'),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Fieldset::make('Filter Data')
                ->schema([
                    Select::make('reseller_status')
                        ->label('Status Reseller')
                        ->placeholder('Semua Pengguna')
                        ->options([
                            'none'     => 'Bukan Reseller',
                            'pending'  => 'Pending Approval',
                            'active'   => 'Reseller Aktif',
                            'rejected' => 'Ditolak',
                        ])
                        ->native(false),

                    DatePicker::make('date_from')
                        ->label('Daftar Dari Tanggal')
                        ->displayFormat('d/m/Y')
                        ->native(false),

                    DatePicker::make('date_until')
                        ->label('Daftar Sampai Tanggal')
                        ->displayFormat('d/m/Y')
                        ->native(false),
                ])
                ->columns(2),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pengguna selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
