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
            ImportColumn::make('is_variant')
                ->label('Varian?')
                ->exampleHeader('Varian? (ya/tidak)')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('variant_name')
                ->label('Nama Varian')
                ->exampleHeader('Nama Varian (misal: Merah XL)')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('sku')
                ->label('SKU')
                ->exampleHeader('SKU')
                ->rules(['nullable', 'string']),
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
                ->exampleHeader('Gambar')
                ->castStateUsing(fn (?string $state) => $state ? array_map('trim', explode(',', $state)) : null),
            ImportColumn::make('price')
                ->label('Harga')
                ->exampleHeader('Harga')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'numeric']),
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
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('is_active')
                ->label('Aktif?')
                ->exampleHeader('Aktif?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('rating')
                ->label('Rating/Bintang')
                ->exampleHeader('Rating/Bintang')
                ->numeric()
                ->rules(['numeric', 'nullable']),
            ImportColumn::make('sold_count')
                ->label('Terjual (Manual)')
                ->exampleHeader('Terjual (Manual)')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('has_free_shipping')
                ->label('Gratis Ongkir?')
                ->exampleHeader('Gratis Ongkir?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('meta_title')
                ->label('Meta Title')
                ->exampleHeader('Meta Title')
                ->rules(['max:255']),
            ImportColumn::make('meta_description')
                ->label('Meta Description')
                ->exampleHeader('Meta Description')
                ->rules(['nullable', 'max:160']),
        ];

        // Dynamically append attribute columns for import
        if (\Illuminate\Support\Facades\Schema::hasTable('attributes')) {
            $attributes = \App\Models\Attribute::all();
            foreach ($attributes as $attr) {
                $columns[] = ImportColumn::make('Attr: ' . $attr->name)
                    ->label('Attr: ' . $attr->name)
                    ->exampleHeader('Attr: ' . $attr->name)
                    ->rules(['nullable', 'string'])
                    ->fillRecordUsing(fn () => null); // we handle it manually in saveRecord
            }
        }

        return $columns;
    }

    public function resolveRecord(): Product
    {
        $slug = filled($this->data['slug'] ?? null) 
            ? $this->data['slug'] 
            : \Illuminate\Support\Str::slug($this->data['name'] ?? 'produk');

        // Jika ini varian, kembalikan dummy model agar validasi Filament tidak error, 
        // tapi nanti di saveRecord kita cegah penyimpanannya.
        $isVariant = in_array(strtolower((string)($this->data['is_variant'] ?? '')), ['1', 'ya', 'varian', 'yes', 'true']);
        
        if ($isVariant) {
            return new Product();
        }

        // Logika slug unik agar tidak bentrok jika nama sama
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            // Jika slug sudah ada, dan ini adalah update produk yang sama, biarkan.
            if (Product::where('slug', $slug)->first()->name === ($this->data['name'] ?? '')) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return Product::firstOrNew([
            'slug' => $slug,
        ]);
    }

    public function saveRecord(): void
    {
        $isVariant = in_array(strtolower((string)($this->data['is_variant'] ?? '')), ['1', 'ya', 'varian', 'yes', 'true']);

        if ($isVariant) {
            $parent = \App\Models\Product::where('name', $this->data['name'])->latest()->first();
            
            if ($parent) {
                // Pastikan parent tertandai punya varian
                $parent->update(['has_variants' => true]);

                // Cari varian berdasarkan SKU atau Nama Varian
                $variantQuery = \App\Models\ProductVariant::where('product_id', $parent->id);
                if (filled($this->data['sku'] ?? null)) {
                    $variantQuery->where('sku', $this->data['sku']);
                } else {
                    $variantQuery->where('name', $this->data['variant_name'] ?? 'Default');
                }
                
                \Illuminate\Support\Facades\Log::info('Import Data: ', $this->data);

                $variant = $variantQuery->first() ?? new \App\Models\ProductVariant();

                $variant->product_id = $parent->id;
                $variant->name = $this->data['variant_name'] ?? 'Default';
                $variant->sku = $this->data['sku'] ?? null;
                $variant->price = filled($this->data['price'] ?? null) ? $this->data['price'] : null;
                $variant->discount_price = filled($this->data['discount_price'] ?? null) ? $this->data['discount_price'] : null;
                $variant->purchase_price = filled($this->data['purchase_price'] ?? null) ? $this->data['purchase_price'] : null;
                $variant->reseller_price = filled($this->data['reseller_price'] ?? null) ? $this->data['reseller_price'] : null;
                $variant->weight = $this->data['weight'] ?? 1000;
                $variant->stock = $this->data['stock'] ?? 0;
                $variant->minimum_stock = $this->data['minimum_stock'] ?? null;
                $variant->is_active = $this->data['is_active'] ?? true;
                $variant->save();

                // Assign dynamic attributes
                $attributes = \Illuminate\Support\Facades\Schema::hasTable('attributes') ? \App\Models\Attribute::all() : collect();
                $optionIdsToSync = [];
                foreach ($attributes as $attr) {
                    $columnName = 'Attr: ' . $attr->name;
                    if (isset($this->data[$columnName]) && filled($this->data[$columnName])) {
                        $val = $this->data[$columnName];
                        $optionSlug = \Illuminate\Support\Str::slug($val);
                        if (empty($optionSlug)) {
                            $optionSlug = 'default';
                        }
                        $attributeOption = \App\Models\AttributeOption::firstOrCreate(
                            ['attribute_id' => $attr->id, 'slug' => $optionSlug],
                            ['value' => $val, 'meta' => null]
                        );
                        $optionIdsToSync[] = $attributeOption->id;
                    }
                }
                
                if (!empty($optionIdsToSync)) {
                    $variant->attributeOptions()->sync($optionIdsToSync);
                } else {
                    $variant->attributeOptions()->detach();
                }
            }
            return; // Jangan simpan produk dummy-nya
        }

        if (!filled($this->record->price)) {
            $this->record->price = 0;
        }

        $this->record->save();

        foreach ($this->getCachedColumns() as $column) {
            $columnName = $column->getName();

            if (blank($this->columnMap[$columnName] ?? null)) {
                continue;
            }

            if (! array_key_exists($columnName, $this->data)) {
                continue;
            }

            $state = $this->data[$columnName];

            if (blank($state) && $column->isBlankStateIgnored()) {
                continue;
            }

            $column->saveRelationships($state);
        }
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
