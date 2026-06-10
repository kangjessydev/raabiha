<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('order_number')->label('No. Pesanan'),
            ExportColumn::make('user.name')->label('Nama Pelanggan'),
            ExportColumn::make('status')->label('Status Pesanan'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('shipping_cost')->label('Ongkos Kirim'),
            ExportColumn::make('discount_total')->label('Diskon'),
            ExportColumn::make('grand_total')->label('Total Bayar'),
            ExportColumn::make('payment_method')->label('Metode Pembayaran'),
            ExportColumn::make('payment_status')->label('Status Pembayaran'),
            ExportColumn::make('shipping_address')->label('Alamat Pengiriman'),
            ExportColumn::make('courier')->label('Kurir'),
            ExportColumn::make('awb_number')->label('No. Resi'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('created_at')->label('Tanggal Pesan'),
            ExportColumn::make('updated_at')->label('Terakhir Diperbarui'),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Fieldset::make('Filter Data')
                ->schema([
                    Select::make('status')
                        ->label('Status Pesanan')
                        ->placeholder('Semua Status')
                        ->options([
                            'pending'    => 'Pending',
                            'processing' => 'Diproses',
                            'shipped'    => 'Dikirim',
                            'completed'  => 'Selesai',
                            'cancelled'  => 'Dibatalkan',
                        ])
                        ->native(false),

                    Select::make('payment_status')
                        ->label('Status Pembayaran')
                        ->placeholder('Semua')
                        ->options([
                            'pending' => 'Belum Bayar',
                            'paid'    => 'Lunas',
                            'failed'  => 'Gagal',
                        ])
                        ->native(false),

                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->displayFormat('d/m/Y')
                        ->native(false),

                    DatePicker::make('date_until')
                        ->label('Sampai Tanggal')
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

    public function getQuery(Builder $query, array $options): Builder
    {
        return $query
            ->when(filled($options['status'] ?? null), fn ($q) => $q->where('status', $options['status']))
            ->when(filled($options['payment_status'] ?? null), fn ($q) => $q->where('payment_status', $options['payment_status']))
            ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
            ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']));
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pesanan selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
