<?php

namespace App\Filament\Exports;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('name')->label('Nama Produk'),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('description')->label('Deskripsi'),
            ExportColumn::make('images')->label('Gambar'),
            ExportColumn::make('price')->label('Harga'),
            ExportColumn::make('discount_price')->label('Harga Diskon'),
            ExportColumn::make('reseller_price')->label('Harga Reseller'),
            ExportColumn::make('purchase_price')->label('Harga Beli (HPP)'),
            ExportColumn::make('stock')->label('Stok'),
            ExportColumn::make('minimum_stock')->label('Stok Minimum'),
            ExportColumn::make('weight')->label('Berat (gram)'),
            ExportColumn::make('has_variants')->label('Punya Varian?'),
            ExportColumn::make('is_active')->label('Aktif?'),
            ExportColumn::make('meta_title')->label('Meta Title'),
            ExportColumn::make('meta_description')->label('Meta Description'),
            ExportColumn::make('wholesale_pricing')->label('Aturan Grosir'),
            ExportColumn::make('promo_rules')->label('Aturan Promo'),
            ExportColumn::make('created_at')->label('Dibuat'),
            ExportColumn::make('updated_at')->label('Diperbarui'),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Fieldset::make('Filter Data')
                ->schema([
                    Select::make('category_id')
                        ->label('Kategori')
                        ->placeholder('Semua Kategori')
                        ->options(Category::pluck('name', 'id'))
                        ->native(false),

                    Select::make('is_active')
                        ->label('Status Produk')
                        ->placeholder('Semua Status')
                        ->options([
                            '1' => 'Aktif',
                            '0' => 'Non-Aktif',
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

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor produk selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
