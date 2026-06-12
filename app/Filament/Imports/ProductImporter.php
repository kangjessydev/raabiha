<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('category')
                ->relationship('category', 'name')
                ->label('Kategori')
                ->exampleHeader('Kategori'),
            ImportColumn::make('name')
                ->label('Nama Produk')
                ->exampleHeader('Nama Produk')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->label('Slug')
                ->exampleHeader('Slug')
                ->rules(['nullable', 'max:255'])
                ->fillRecordUsing(function (\App\Models\Product $record, ?string $state, array $data): void {
                    $record->slug = filled($state) ? $state : \Illuminate\Support\Str::slug($data['name']);
                }),
            ImportColumn::make('description')
                ->label('Deskripsi')
                ->exampleHeader('Deskripsi'),
            ImportColumn::make('images')
                ->label('Gambar')
                ->exampleHeader('Gambar'),
            ImportColumn::make('price')
                ->label('Harga')
                ->exampleHeader('Harga')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('discount_price')
                ->label('Harga Diskon')
                ->exampleHeader('Harga Diskon')
                ->numeric()
                ->rules(['numeric', 'nullable']),
            ImportColumn::make('reseller_price')
                ->label('Harga Reseller')
                ->exampleHeader('Harga Reseller')
                ->numeric()
                ->rules(['numeric', 'nullable']),
            ImportColumn::make('purchase_price')
                ->label('Harga Beli (HPP)')
                ->exampleHeader('Harga Beli (HPP)')
                ->numeric()
                ->rules(['numeric', 'nullable']),
            ImportColumn::make('stock')
                ->label('Stok')
                ->exampleHeader('Stok')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('minimum_stock')
                ->label('Stok Minimum')
                ->exampleHeader('Stok Minimum')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('weight')
                ->label('Berat (gram)')
                ->exampleHeader('Berat (gram)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('has_variants')
                ->label('Punya Varian?')
                ->exampleHeader('Punya Varian?')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_active')
                ->label('Aktif?')
                ->exampleHeader('Aktif?')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('meta_title')
                ->label('Meta Title')
                ->exampleHeader('Meta Title')
                ->rules(['max:255']),
            ImportColumn::make('meta_description')
                ->label('Meta Description')
                ->exampleHeader('Meta Description'),
            ImportColumn::make('wholesale_pricing')
                ->label('Aturan Grosir')
                ->exampleHeader('Aturan Grosir'),
            ImportColumn::make('promo_rules')
                ->label('Aturan Promo')
                ->exampleHeader('Aturan Promo'),
        ];
    }

    public function resolveRecord(): Product
    {
        $slug = filled($this->data['slug'] ?? null) 
            ? $this->data['slug'] 
            : \Illuminate\Support\Str::slug($this->data['name']);

        // Gunakan slug sebagai penentu: jika ada update, jika tidak buat baru
        return Product::firstOrNew([
            'slug' => $slug,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
