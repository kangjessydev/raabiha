<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_products')
                ->label('Export')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->action(function () {
                    $query = \App\Models\Product::with(['category', 'variants']);
                    
                    $csv = \League\Csv\Writer::createFromString('');
                    $csv->insertOne([
                        'ID', 'Varian?', 'Nama Varian', 'SKU Varian', 'Kategori', 'Nama Produk', 'Slug', 'Deskripsi', 'Gambar', 'Harga', 'Harga Diskon', 'Harga Beli (HPP)', 'Harga Reseller', 'Stok', 'Stok Minimum', 'Berat (gram)', 'Punya Varian?', 'Aktif?', 'Rating/Bintang', 'Terjual (Manual)', 'Gratis Ongkir?', 'Meta Title', 'Meta Description', 'Dibuat', 'Diperbarui'
                    ]);

                    foreach ($query->cursor() as $product) {
                        $csv->insertOne([
                            $product->id,
                            'Tidak',
                            '',
                            '',
                            $product->category?->name ?? '',
                            $product->name,
                            $product->slug,
                            trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description))),
                            is_array($product->images) ? implode(', ', $product->images) : $product->images,
                            $product->price,
                            $product->discount_price,
                            $product->purchase_price,
                            $product->reseller_price,
                            $product->stock,
                            $product->minimum_stock,
                            $product->weight,
                            $product->has_variants ? 1 : 0,
                            $product->is_active ? 1 : 0,
                            $product->rating,
                            $product->sold_count,
                            $product->has_free_shipping ? 1 : 0,
                            $product->meta_title,
                            trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->meta_description))),
                            $product->created_at?->format('Y-m-d H:i:s'),
                            $product->updated_at?->format('Y-m-d H:i:s')
                        ]);

                        foreach ($product->variants as $variant) {
                            $csv->insertOne([
                                $variant->id,
                                'Ya',
                                $variant->name,
                                $variant->sku,
                                $product->category?->name ?? '',
                                $product->name, // Parent name
                                '',
                                '',
                                '',
                                $variant->price,
                                $variant->discount_price,
                                $variant->purchase_price,
                                $variant->reseller_price,
                                $variant->stock,
                                $variant->minimum_stock,
                                $variant->weight,
                                '',
                                $variant->is_active ? 1 : 0,
                                '',
                                '',
                                '',
                                '',
                                '',
                                $variant->created_at?->format('Y-m-d H:i:s'),
                                $variant->updated_at?->format('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv->toString();
                    }, 'products-export-' . date('Y-m-d-His') . '.csv');
                }),
            \Filament\Actions\ImportAction::make('import_raabiha')
                ->label('Import (Format Asli)')
                ->importer(\App\Filament\Imports\ProductImporter::class)
                ->modalDescription(new \Illuminate\Support\HtmlString('
                    <div class="prose text-sm text-gray-600 dark:text-gray-400 mt-2">
                        <p><strong>Panduan Pengisian Excel:</strong></p>
                        <ul class="list-disc pl-4 space-y-1 mt-1">
                            <li><strong>Produk Utama (Varian? = Tidak):</strong><br> Wajib isi: Nama Produk, Harga, Stok, Berat, Punya Varian?, dan Aktif?.</li>
                            <li><strong>Varian Anak (Varian? = Ya):</strong><br> Wajib isi: Nama Produk <i>(harus sama dengan nama induknya)</i>, Nama Varian, Harga, Stok, Berat. <i>(Punya Varian?, Kategori, Deskripsi dll boleh dikosongkan)</i>.</li>
                        </ul>
                    </div>
                ')),
            \Filament\Actions\ImportAction::make('import_client')
                ->label('Import dari Klien (Gabungan)')
                ->importer(\App\Filament\Imports\ClientProductImporter::class)
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray'),
            CreateAction::make(),
        ];
    }
}
