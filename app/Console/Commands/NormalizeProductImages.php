<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageResolver;
use Illuminate\Console\Command;

class NormalizeProductImages extends Command
{
    protected $signature = 'products:normalize-images
                            {--dry-run : Jalankan simulasi tanpa menyimpan perubahan ke database}';

    protected $description = 'Menormalisasi kolom products.images dari string URL/path kembali ke ID Media Curator secara idempoten (zero data loss)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('Mode DRY-RUN aktif: tidak ada perubahan yang akan ditulis ke database.');
        }

        $scanned = 0;
        $updated = 0;
        $convertedItems = 0;
        $unmatchedItems = 0;

        Product::query()
            ->whereNotNull('images')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($dryRun, &$scanned, &$updated, &$convertedItems, &$unmatchedItems): void {
                foreach ($products as $product) {
                    $scanned++;

                    if (! is_array($product->images) || empty($product->images)) {
                        continue;
                    }

                    $result = ProductImageResolver::normalizeImageArray($product->images);

                    if (! empty($result['unmatched'])) {
                        $unmatchedItems += count($result['unmatched']);
                        foreach ($result['unmatched'] as $unmatchedUrl) {
                            $this->warn("[UNMATCHED] Produk #{$product->id} ({$product->name}): {$unmatchedUrl} (dipertahankan agar zero data loss)");
                        }
                    }

                    if (! $result['changed']) {
                        continue;
                    }

                    $updated++;
                    $convertedItems += $result['converted_count'];

                    $tag = $dryRun ? '[DRY-RUN]' : '[UPDATED]';
                    $this->line("{$tag} Produk #{$product->id} ({$product->name}): konversi {$result['converted_count']} gambar -> ".json_encode($result['normalized']));

                    if (! $dryRun) {
                        $product->images = $result['normalized'];
                        $product->save();
                    }
                }
            });

        $this->newLine();
        $this->info('=== Ringkasan Normalisasi Gambar Produk ===');
        $this->line('Mode               : '.($dryRun ? 'DRY-RUN (simulasi)' : 'LIVE (tersimpan)'));
        $this->line("Produk dipindai    : {$scanned}");
        $this->line("Produk diperbarui  : {$updated}");
        $this->line("Item dikonversi    : {$convertedItems}");
        $this->line("Item tak cocok     : {$unmatchedItems}");

        return self::SUCCESS;
    }
}
