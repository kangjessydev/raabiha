<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction; // Filament\Actions\Action in older filament, Filament\Notifications\Actions\Action maybe?
use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class ExportProductsCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    protected User $user;
    protected array $filters;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, array $filters)
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Product::with(['category', 'variants.attributeOptions.attribute']);

        // Apply filters
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $query->where('is_active', $this->filters['is_active']);
        }
        if (!empty($this->filters['channel_visibility'])) {
            $query->where('channel_visibility', $this->filters['channel_visibility']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_until'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_until']);
        }

        $products = $query->get();

        $csv = Writer::from(new SplTempFileObject());
        $csv->setDelimiter(';');

        // Build Headers
        $headers = [
            'Varian?',
            'Nama Varian',
            'SKU',
            'Kategori',
            'Nama Produk',
            'Slug',
            'Deskripsi',
            'Gambar',
            'Harga',
            'Harga Diskon',
            'Harga Reseller',
            'Harga Beli (HPP)',
            'Stok',
            'Stok Minimum',
            'Berat (gram)',
            'Punya Varian?',
            'Aktif?',
            'Rating/Bintang',
            'Terjual (Manual)',
            'Gratis Ongkir?',
            'Tampil Di (Channel)',
            'Asal Produk (Dibuat via POS?)',
            'Harga POS',
            'Harga Diskon POS',
            'Dibuat',
            'Diperbarui',
        ];

        $dynamicAttributes = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('attributes')) {
            $attributes = Attribute::all();
            foreach ($attributes as $attr) {
                $headers[] = 'Attr: ' . $attr->name;
                $dynamicAttributes[] = strtolower(trim($attr->name));
            }
        }

        $csv->insertOne($headers);

        foreach ($products as $product) {
            // Parent Row
            $parentRow = [
                'Tidak',
                '',
                $product->sku ?? '',
                $product->category?->name ?? '',
                $product->name ?? '',
                $product->slug ?? '',
                $this->formatDescription($product->description),
                $this->formatImages($product->images),
                $product->price,
                $product->discount_price,
                $product->reseller_price,
                $product->purchase_price,
                $product->stock,
                $product->minimum_stock,
                $product->weight,
                $product->has_variants ? 'Ya' : 'Tidak',
                $product->is_active ? '1' : '0',
                $product->rating,
                $product->sold_count,
                $product->has_free_shipping ? '1' : '0',
                $product->channel_visibility ?? 'both',
                $product->is_custom ? '1' : '0',
                $product->pos_price,
                $product->pos_discount_price,
                $product->created_at?->toDateTimeString() ?? '',
                $product->updated_at?->toDateTimeString() ?? '',
            ];

            // Add empty columns for dynamic attributes on parent row
            foreach ($dynamicAttributes as $attr) {
                $parentRow[] = '';
            }

            $csv->insertOne($parentRow);

            // Child Variant Rows
            if ($product->has_variants && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $optMap = [];
                    foreach ($variant->attributeOptions as $opt) {
                        if ($opt->attribute) {
                            $optMap[strtolower(trim($opt->attribute->name))] = $opt->value;
                        }
                    }

                    $varRow = [
                        'Ya',
                        $variant->name ?? '',
                        $variant->sku ?? '',
                        '', // Kategori dikosongkan untuk varian
                        '', // Nama Produk dikosongkan untuk varian
                        '', // Slug dikosongkan untuk varian
                        '',
                        '',
                        $variant->getRawOriginal('price') ?? $product->price,
                        $variant->getRawOriginal('discount_price'),
                        $variant->getRawOriginal('reseller_price'),
                        $variant->getRawOriginal('purchase_price'),
                        $variant->stock,
                        $variant->minimum_stock,
                        $variant->weight,
                        'Tidak',
                        $variant->is_active ? '1' : '0',
                        '',
                        '',
                        '',
                        $variant->channel_visibility ?? $product->channel_visibility ?? 'both',
                        $product->is_custom ? '1' : '0',
                        $variant->getRawOriginal('pos_price'),
                        $variant->getRawOriginal('pos_discount_price'),
                        $variant->created_at?->toDateTimeString() ?? '',
                        $variant->updated_at?->toDateTimeString() ?? '',
                    ];

                    foreach ($dynamicAttributes as $attr) {
                        $varRow[] = $optMap[$attr] ?? '';
                    }

                    $csv->insertOne($varRow);
                }
            }
        }

        $fileName = 'products_export_' . now()->format('Y_m_d_His') . '_' . uniqid() . '.csv';
        Storage::disk('public')->put('exports/' . $fileName, $csv->toString());
        
        $url = asset('storage/exports/' . $fileName);

        Notification::make()
            ->title('Ekspor Produk Selesai')
            ->body('File CSV produk beserta variannya telah berhasil dibuat.')
            ->success()
            ->actions([
                Action::make('download')
                    ->label('Download CSV')
                    ->url($url)
                    ->button(),
            ])
            ->sendToDatabase($this->user);
    }

    protected function formatDescription($state)
    {
        if (empty($state)) return '';
        $text = strip_tags($state);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    protected function formatImages($state)
    {
        if (is_array($state) && count($state) > 0) {
            if (is_numeric($state[0])) {
                $mediaPaths = \Awcodes\Curator\Models\Media::whereIn('id', $state)->pluck('path')->toArray();
                $urls = array_map(fn ($path) => asset('storage/' . $path), $mediaPaths);
                return implode(', ', $urls);
            }
            return implode(', ', $state);
        }
        return is_string($state) ? $state : '';
    }
}
