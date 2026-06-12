<?php

namespace App\Filament\Exports;

use App\Models\ProductVariant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductVariantExporter extends Exporter
{
    protected static ?string $model = ProductVariant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('product.name')->label('Nama Produk Induk'),
            ExportColumn::make('name')->label('Nama Varian'),
            ExportColumn::make('sku')->label('SKU'),
            ExportColumn::make('stock')->label('Stok'),
            ExportColumn::make('minimum_stock')->label('Stok Minimum'),
            ExportColumn::make('is_price_override')->label('Harga Berbeda dari Induk?'),
            ExportColumn::make('price')->label('Harga'),
            ExportColumn::make('discount_price')->label('Harga Diskon'),
            ExportColumn::make('reseller_price')->label('Harga Reseller'),
            ExportColumn::make('purchase_price')->label('Harga Beli (HPP)'),
            ExportColumn::make('is_weight_override')->label('Berat Berbeda dari Induk?'),
            ExportColumn::make('weight')->label('Berat (gram)'),
            ExportColumn::make('is_active')->label('Aktif?'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor varian produk selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
