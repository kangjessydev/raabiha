<?php

namespace App\Filament\Imports;

use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

class ClientProductImporter extends Importer
{
    protected static ?string $model = ProductVariant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_produk')
                ->label('Nama Produk')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('deskripsi')
                ->label('Deskripsi')
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('warna')
                ->label('Warna')
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('ukuran')
                ->label('Ukuran')
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('kategori')
                ->label('Kategori')
                ->requiredMapping()
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('sku')
                ->label('SKU')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('bahan')
                ->label('Bahan')
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('harga_jual')
                ->label('Harga Jual')
                ->requiredMapping()
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('berat_per_kg')
                ->label('Berat (Kg)')
                ->requiredMapping()
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('hpp')
                ->label('HPP')
                ->requiredMapping()
                ->fillRecordUsing(fn() => null),
        ];
    }

    public function resolveRecord(): ?ProductVariant
    {
        $productName = $this->data['nama_produk'] ?? '';
        $categoryName = $this->data['kategori'] ?? '';
        $material = $this->data['bahan'] ?? '';
        $deskripsiTeks = $this->data['deskripsi'] ?? '';
        
        $priceStr = (string) ($this->data['harga_jual'] ?? '0');
        $cogsStr = (string) ($this->data['hpp'] ?? '0');
        
        $price = (float) str_replace(['.', ','], '', $priceStr);
        $cogs = (float) str_replace(['.', ','], '', $cogsStr);

        $weightStr = strtolower(trim((string) ($this->data['berat_per_kg'] ?? '0')));
        $weightInGrams = 0;
        if (str_contains($weightStr, 'kg')) {
            $weightInGrams = ((float) str_replace('kg', '', $weightStr)) * 1000;
        } elseif (str_contains($weightStr, 'gr')) {
            $weightInGrams = (float) str_replace('gr', '', $weightStr);
        } else {
            $weightInGrams = ((float) $weightStr) * 1000;
        }

        $sku = $this->data['sku'] ?? '';
        $color = $this->data['warna'] ?? '';
        $size = $this->data['ukuran'] ?? '';

        $category = Category::firstOrCreate(
            ['name' => $categoryName], 
            ['slug' => Str::slug($categoryName)]
        );

        $finalDescription = "Bahan: " . $material;
        if (!empty($deskripsiTeks)) {
            $finalDescription = $deskripsiTeks . "<br><br>" . $finalDescription;
        }

        $product = Product::firstOrCreate(
            ['name' => $productName],
            [
                'slug' => Str::slug($productName),
                'category_id' => $category->id,
                'has_variants' => true,
                'description' => $finalDescription,
                'price' => $price,
                'purchase_price' => $cogs,
                'weight' => $weightInGrams,
                'stock' => 0,
                'is_active' => true,
            ]
        );

        $variantName = trim($color . ' ' . $size);
        if (empty($variantName)) {
            $variantName = 'Default';
        }

        return ProductVariant::firstOrNew(
            ['sku' => $sku],
            [
                'product_id' => $product->id,
                'name' => $variantName,
                'is_price_override' => true,
                'price' => $price,
                'purchase_price' => $cogs,
                'is_weight_override' => true,
                'weight' => $weightInGrams,
                'stock' => 10,
                'is_active' => true,
            ]
        );
    }

    protected function afterSave(): void
    {
        $color = trim($this->data['warna'] ?? '');
        $size = trim($this->data['ukuran'] ?? '');
        $material = trim($this->data['bahan'] ?? '');
        $optionIds = [];

        if (!empty($color)) {
            $attributeWarna = \App\Models\Attribute::firstOrCreate(
                ['name' => 'Warna'],
                ['slug' => 'warna', 'type' => 'color']
            );
            $optionWarna = \App\Models\AttributeOption::firstOrCreate(
                ['attribute_id' => $attributeWarna->id, 'value' => $color],
                ['slug' => Str::slug($color)]
            );
            $optionIds[] = $optionWarna->id;
        }

        if (!empty($size)) {
            $attributeUkuran = \App\Models\Attribute::firstOrCreate(
                ['name' => 'Ukuran'],
                ['slug' => 'ukuran', 'type' => 'text']
            );
            $optionUkuran = \App\Models\AttributeOption::firstOrCreate(
                ['attribute_id' => $attributeUkuran->id, 'value' => $size],
                ['slug' => Str::slug($size)]
            );
            $optionIds[] = $optionUkuran->id;
        }

        if (!empty($material)) {
            $attributeBahan = \App\Models\Attribute::firstOrCreate(
                ['name' => 'Bahan'],
                ['slug' => 'bahan', 'type' => 'text']
            );
            $optionBahan = \App\Models\AttributeOption::firstOrCreate(
                ['attribute_id' => $attributeBahan->id, 'value' => $material],
                ['slug' => Str::slug($material)]
            );
            $optionIds[] = $optionBahan->id;
        }

        if (count($optionIds) > 0) {
            $this->record->attributeOptions()->syncWithoutDetaching($optionIds);
        }

        // Automatic Image Matching Logic
        $product = $this->record->product;
        if ($product) {
            // Find any media that matches the SKU or the Product Slug
            $sku = strtolower($this->record->sku);
            $slug = strtolower($product->slug);
            
            $mediaIds = \App\Models\Media::where('name', 'like', "%{$sku}%")
                ->orWhere('name', 'like', "%{$slug}%")
                ->pluck('id')
                ->toArray();

            if (count($mediaIds) > 0) {
                $existingImages = $product->images ?? [];
                // Merge and remove duplicates
                $newImages = array_values(array_unique(array_merge($existingImages, $mediaIds)));
                
                // Only update if there are new images to prevent unnecessary DB queries
                if (count($newImages) !== count($existingImages)) {
                    $product->update(['images' => $newImages]);
                }
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import Produk Klien selesai. ' . Number::format($import->successful_rows) . ' ' . str('baris')->plural($import->successful_rows) . ' berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diimpor.';
        }

        return $body;
    }
}
