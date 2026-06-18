<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Filament\Exports\ProductExporter;

class TestExportValidationCommand extends Command
{
    protected $signature = 'test:export-validation {type}';
    protected $description = 'Automated test to validate Filament Exporter logic for all types';

    public function handle()
    {
        $type = $this->argument('type');
        $this->info("Memulai Uji Coba Validasi Export: " . strtoupper($type));

        $config = [
            'product' => ['model' => \App\Models\Product::class, 'exporter' => \App\Filament\Exports\ProductExporter::class],
            'order' => ['model' => \App\Models\Order::class, 'exporter' => \App\Filament\Exports\OrderExporter::class],
            'user' => ['model' => \App\Models\User::class, 'exporter' => \App\Filament\Exports\UserExporter::class],
            'category' => ['model' => \App\Models\Category::class, 'exporter' => \App\Filament\Exports\CategoryExporter::class],
            'post' => ['model' => \App\Models\Post::class, 'exporter' => \App\Filament\Exports\PostExporter::class],
            'voucher' => ['model' => \App\Models\Voucher::class, 'exporter' => \App\Filament\Exports\VoucherExporter::class],
        ];

        if (!isset($config[$type])) {
            $this->error("Tipe tidak valid. Pilih: product, order, user, category, post, voucher");
            return;
        }

        $modelClass = $config[$type]['model'];
        $exporterClass = $config[$type]['exporter'];

        // Ambil sampel data terakhir
        $record = $modelClass::latest()->first();

        if (!$record) {
            $this->warn("Tidak ada data di tabel {$type} untuk diuji.");
            return;
        }

        $this->info("Menguji data ID: {$record->id}");

        try {
            $columns = $exporterClass::getColumns();
            $this->info("\n--- Resolusi Kolom Export ---");
            
            foreach ($columns as $column) {
                $columnName = $column->getName();
                $rawValue = data_get($record, $columnName);
                
                // Coba jalankan formatStateUsing jika ada via Reflection
                $formattedValue = $rawValue;
                try {
                    $reflection = new \ReflectionClass($column);
                    $property = $reflection->getProperty('formatStateUsing');
                    $property->setAccessible(true);
                    $formatStateUsing = $property->getValue($column);
                    if ($formatStateUsing) {
                        $formattedValue = app()->call($formatStateUsing, ['state' => $rawValue, 'record' => $record]);
                    }
                } catch (\Exception $e) {
                    // Ignore
                }

                if (is_array($formattedValue)) {
                    $this->warn(str_pad($columnName, 25) . " : [ARRAY DETECTED!] -> " . json_encode($formattedValue));
                } else {
                    $displayValue = strlen((string)$formattedValue) > 60 ? substr((string)$formattedValue, 0, 60) . '...' : (string)$formattedValue;
                    $this->line(str_pad($columnName, 25) . " : " . $displayValue);
                }
            }
            $this->info("\n[SELESAI] Uji coba {$type} berhasil tanpa fatal error.");
        } catch (\Exception $e) {
            $this->error("\n[GAGAL] Terjadi Fatal Error: " . $e->getMessage());
        }
    }
}
