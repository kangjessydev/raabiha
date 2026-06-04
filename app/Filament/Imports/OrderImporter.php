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
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('order_number')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('subtotal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('shipping_cost')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('discount_total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('grand_total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('payment_method')
                ->rules(['max:255']),
            ImportColumn::make('payment_status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('shipping_address'),
            ImportColumn::make('courier')
                ->rules(['max:255']),
            ImportColumn::make('awb_number')
                ->rules(['max:255']),
            ImportColumn::make('notes'),
        ];
    }

    public function resolveRecord(): Order
    {
        return new Order();
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
