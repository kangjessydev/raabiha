<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use App\Filament\Clusters\MediaFiles;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Exports\OrderExporter;
use App\Filament\Exports\PostExporter;
use App\Filament\Exports\ProductExporter;
use App\Filament\Exports\UserExporter;
use App\Filament\Exports\VoucherExporter;
use BackedEnum;
use Filament\Actions\ExportAction;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ExportMedia extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected string $view = 'filament.clusters.media-files.pages.export-media';

    protected static ?string $cluster = MediaFiles::class;

    public static function getNavigationLabel(): string
    {
        return 'Ekspor Data';
    }

    public function getTitle(): string
    {
        return 'Ekspor Data';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_products')
                ->label('Ekspor Produk')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->form([
                    \Filament\Schemas\Components\Fieldset::make('Filter Data')
                        ->schema([
                            \Filament\Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->placeholder('Semua Kategori')
                                ->options(\App\Models\Category::pluck('name', 'id'))
                                ->native(false),
                            \Filament\Forms\Components\Select::make('is_active')
                                ->label('Status Produk')
                                ->placeholder('Semua Status')
                                ->options(['1' => 'Aktif', '0' => 'Non-Aktif'])
                                ->native(false),
                        ]),
                    \Filament\Schemas\Components\Fieldset::make('Filter Tanggal (Opsional)')
                        ->schema([
                            \Filament\Forms\Components\DatePicker::make('date_from')->label('Dari Tanggal'),
                            \Filament\Forms\Components\DatePicker::make('date_until')->label('Sampai Tanggal'),
                        ]),
                ])
                ->action(function (array $data) {
                    $query = \App\Models\Product::with(['category', 'variants.attributeOptions.attribute']);
                    
                    if (filled($data['category_id'] ?? null)) {
                        $query->where('category_id', $data['category_id']);
                    }
                    if (isset($data['is_active']) && $data['is_active'] !== '') {
                        $query->where('is_active', (bool) $data['is_active']);
                    }
                    if (filled($data['date_from'] ?? null)) {
                        $query->whereDate('created_at', '>=', $data['date_from']);
                    }
                    if (filled($data['date_until'] ?? null)) {
                        $query->whereDate('created_at', '<=', $data['date_until']);
                    }

                    $attributes = \Illuminate\Support\Facades\Schema::hasTable('attributes') ? \App\Models\Attribute::all() : collect();
                    $attributeHeaders = $attributes->map(fn($attr) => 'Attr: ' . $attr->name)->toArray();

                    $headers = array_merge(
                        ['ID', 'Varian?', 'Nama Varian', 'SKU', 'Kategori', 'Nama Produk', 'Slug', 'Deskripsi', 'Gambar', 'Harga', 'Harga Diskon', 'Harga Beli (HPP)', 'Harga Reseller', 'Stok', 'Stok Minimum', 'Berat (gram)', 'Punya Varian?'],
                        $attributeHeaders,
                        ['Aktif?', 'Rating/Bintang', 'Terjual (Manual)', 'Gratis Ongkir?', 'Meta Title', 'Meta Description', 'Dibuat', 'Diperbarui']
                    );

                    $csv = \League\Csv\Writer::createFromString('');
                    $csv->insertOne($headers);

                    foreach ($query->cursor() as $product) {
                        $csv->insertOne(array_merge(
                            [
                                $product->id,
                                'Tidak',
                                '',
                                $product->sku,
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
                            ],
                            array_fill(0, count($attributeHeaders), ''),
                            [
                                $product->is_active ? 1 : 0,
                                $product->rating,
                                $product->sold_count,
                                $product->has_free_shipping ? 1 : 0,
                                $product->meta_title,
                                trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->meta_description))),
                                $product->created_at?->format('Y-m-d H:i:s'),
                                $product->updated_at?->format('Y-m-d H:i:s')
                            ]
                        ));

                        foreach ($product->variants as $variant) {
                            $variantOptions = [];
                            foreach ($attributes as $attr) {
                                $option = $variant->attributeOptions->firstWhere('attribute_id', $attr->id);
                                $variantOptions[] = $option ? $option->value : '';
                            }

                            $csv->insertOne(array_merge(
                                [
                                    $variant->id,
                                    'Ya',
                                    $variant->name,
                                    $variant->sku,
                                    $product->category?->name ?? '',
                                    $product->name, // Parent name
                                    '',
                                    '',
                                    '',
                                    $variant->getRawOriginal('price'),
                                    $variant->getRawOriginal('discount_price'),
                                    $variant->getRawOriginal('purchase_price'),
                                    $variant->getRawOriginal('reseller_price'),
                                    $variant->stock,
                                    $variant->minimum_stock,
                                    $variant->weight,
                                    '',
                                ],
                                $variantOptions,
                                [
                                    $variant->is_active ? 1 : 0,
                                    '',
                                    '',
                                    '',
                                    '',
                                    '',
                                    $variant->created_at?->format('Y-m-d H:i:s'),
                                    $variant->updated_at?->format('Y-m-d H:i:s')
                                ]
                            ));
                        }
                    }

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv->toString();
                    }, 'products-export-' . date('Y-m-d-His') . '.csv');
                }),

            ExportAction::make('export_orders')
                ->label('Ekspor Pesanan')
                ->exporter(OrderExporter::class)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->modifyQueryUsing(fn (Builder $query, array $options) => $query
                    ->when(filled($options['status'] ?? null), fn ($q) => $q->where('status', $options['status']))
                    ->when(filled($options['payment_status'] ?? null), fn ($q) => $q->where('payment_status', $options['payment_status']))
                    ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
                    ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']))
                ),

            ExportAction::make('export_users')
                ->label('Ekspor Pengguna')
                ->exporter(UserExporter::class)
                ->icon('heroicon-o-users')
                ->color('info')
                ->modifyQueryUsing(fn (Builder $query, array $options) => $query
                    ->when(filled($options['reseller_status'] ?? null), fn ($q) => $options['reseller_status'] === 'none'
                        ? $q->where('is_reseller', false)
                        : $q->where('is_reseller', true)->where('reseller_status', $options['reseller_status'])
                    )
                    ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
                    ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']))
                ),

            ExportAction::make('export_categories')
                ->label('Ekspor Kategori')
                ->exporter(CategoryExporter::class)
                ->icon('heroicon-o-folder')
                ->color('warning')
                ->hidden(),

            ExportAction::make('export_posts')
                ->label('Ekspor Artikel Blog')
                ->exporter(PostExporter::class)
                ->icon('heroicon-o-document-text')
                ->color('gray'),

            ExportAction::make('export_vouchers')
                ->label('Ekspor Voucher')
                ->exporter(VoucherExporter::class)
                ->icon('heroicon-o-ticket')
                ->color('danger')
                ->hidden(),
        ];
    }
}
