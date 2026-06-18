<?php

namespace App\Filament\Imports;

use App\Models\Order;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class OrderImporter extends Importer
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user')
                ->label('Nama Pembeli')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('order_number')
                ->label('Nomor Pesanan')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->label('Status Pesanan')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('source')
                ->rules(['max:255']),
            ImportColumn::make('voucher')
                ->relationship('voucher', 'code')
                ->rules(['nullable']),
            ImportColumn::make('subtotal')
                ->label('Subtotal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('shipping_cost')
                ->label('Ongkir')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('discount_total')
                ->label('Diskon')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('grand_total')
                ->label('Total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('payment_method')
                ->label('Metode Pembayaran')
                ->rules(['max:255']),
            ImportColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('shipping_address')
                ->label('Alamat')
                ->castStateUsing(fn (?string $state) => $state ? ['full_address' => $state] : null),
            ImportColumn::make('courier')
                ->label('Kurir')
                ->rules(['max:255']),
            ImportColumn::make('awb_number')
                ->label('Resi')
                ->rules(['max:255']),
            ImportColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    public function resolveRecord(): Order
    {
        return Order::firstOrNew([
            'order_number' => $this->data['order_number'] ?? null,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your order import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
