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
            ExportColumn::make('is_variant')
                ->label('Varian?')
                ->state(fn() => 'Tidak'),
            ExportColumn::make('variant_name')
                ->label('Nama Varian')
                ->state(fn() => ''),
            ExportColumn::make('variant_sku')
                ->label('SKU Varian')
                ->state(fn() => ''),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('name')->label('Nama Produk'),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('description')
                ->label('Deskripsi')
                ->formatStateUsing(function ($state) {
                    if (empty($state)) return null;
                    $text = strip_tags($state);
                    return trim(preg_replace('/\s+/', ' ', $text));
                }),
            ExportColumn::make('images')
                ->label('Gambar')
                ->formatStateUsing(function ($state) {
                    if (is_array($state) && count($state) > 0) {
                        // Check if they are Curator media IDs
                        if (is_numeric($state[0])) {
                            $mediaPaths = \Awcodes\Curator\Models\Media::whereIn('id', $state)->pluck('path')->toArray();
                            $urls = array_map(fn($path) => asset('storage/' . $path), $mediaPaths);
                            return implode(', ', $urls);
                        }
                        
                        // If they are just normal string paths/urls
                        return implode(', ', $state);
                    }
                    return $state;
                }),
            ExportColumn::make('price')->label('Harga'),
            ExportColumn::make('discount_price')->label('Harga Diskon'),
            ExportColumn::make('reseller_price')->label('Harga Reseller'),
            ExportColumn::make('purchase_price')->label('Harga Beli (HPP)'),
            ExportColumn::make('stock')->label('Stok'),
            ExportColumn::make('minimum_stock')->label('Stok Minimum'),
            ExportColumn::make('weight')->label('Berat (gram)'),
            ExportColumn::make('has_variants')->label('Punya Varian?'),
            ExportColumn::make('is_active')->label('Aktif?'),
            ExportColumn::make('rating')->label('Rating/Bintang'),
            ExportColumn::make('sold_count')->label('Terjual (Manual)'),
            ExportColumn::make('has_free_shipping')->label('Gratis Ongkir?'),

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
