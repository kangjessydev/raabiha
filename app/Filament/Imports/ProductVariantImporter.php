<?php

namespace App\Filament\Imports;

use App\Models\ProductVariant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductVariantImporter extends Importer
{
    protected static ?string $model = ProductVariant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product')
                ->relationship('product', 'name')
                ->label('Nama Produk Induk')
                ->exampleHeader('Nama Produk Induk')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('name')
                ->label('Nama Varian')
                ->exampleHeader('Nama Varian')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('sku')
                ->label('SKU')
                ->exampleHeader('SKU')
                ->rules(['nullable', 'max:255']),
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
            ImportColumn::make('is_price_override')
                ->label('Harga Berbeda dari Induk?')
                ->exampleHeader('Harga Berbeda dari Induk?')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('price')
                ->label('Harga')
                ->exampleHeader('Harga')
                ->numeric()
                ->rules(['numeric', 'nullable']),
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
            ImportColumn::make('is_weight_override')
                ->label('Berat Berbeda dari Induk?')
                ->exampleHeader('Berat Berbeda dari Induk?')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('weight')
                ->label('Berat (gram)')
                ->exampleHeader('Berat (gram)')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('is_active')
                ->label('Aktif?')
                ->exampleHeader('Aktif?')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ProductVariant
    {
        // Try to update existing variant using SKU if it's provided.
        // Otherwise try to find by product and variant name.
        if (!empty($this->data['sku'])) {
            $variant = ProductVariant::where('sku', $this->data['sku'])->first();
            if ($variant) {
                return $variant;
            }
        }

        if (!empty($this->data['product_id']) && !empty($this->data['name'])) {
            $variant = ProductVariant::where('product_id', $this->data['product_id'])
                ->where('name', $this->data['name'])
                ->first();
            if ($variant) {
                return $variant;
            }
        }

        return new ProductVariant();
    }

    protected function afterSave(): void
    {
        $variant = $this->record;
        
        // Coba ekstrak opsi atribut dari nama varian
        // Asumsi format: "Nama Produk - Warna - Ukuran"
        $parts = explode(' - ', $variant->name);
        
        if (count($parts) > 1) {
            // Hapus bagian pertama (nama produk)
            array_shift($parts);
            
            // Sisa parts adalah nama opsi (contoh: ["Merah", "XL"])
            $optionIds = \App\Models\AttributeOption::whereIn('value', $parts)->pluck('id');
            
            if ($optionIds->isNotEmpty()) {
                // Hubungkan varian dengan opsi atribut secara otomatis
                $variant->attributeOptions()->sync($optionIds);
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor varian produk selesai dan ' . Number::format($import->successful_rows) . ' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal diimpor.';
        }

        return $body;
    }
}
