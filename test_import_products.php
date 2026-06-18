<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Jobs\ImportCsv;
use App\Filament\Imports\ProductImporter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;
use League\Csv\Writer;
use League\Csv\Statement;
use League\Csv\Reader;

echo "1. Mempersiapkan Data CSV Produk...\n";

// Buat dummy CSV
$csvPath = 'imports/test_products.csv';
$columns = ProductImporter::getColumns();
$headers = array_map(fn($col) => $col->getName(), $columns);

if (!is_dir(storage_path('app/imports'))) {
    mkdir(storage_path('app/imports'), 0755, true);
}
$writer = Writer::createFromPath(storage_path('app/' . $csvPath), 'w+');
$writer->insertOne($headers);

function createRow($data, $headers) {
    $row = [];
    foreach ($headers as $header) {
        $row[] = $data[$header] ?? null;
    }
    return $row;
}

// Data benar
$writer->insertOne(createRow([
    'category' => null, // Biarkan kosong atau isi ID jika ada
    'name' => 'Produk A Test',
    'slug' => 'produk-a-test',
    'description' => 'Deskripsi A',
    'price' => 10000,
    'reseller_price' => 9000,
    'purchase_price' => 8000,
    'stock' => 10,
    'minimum_stock' => 1,
    'weight' => 100,
    'has_variants' => 0,
    'is_active' => 1,
    'rating' => 5,
    'sold_count' => 2,
    'has_free_shipping' => 1
], $headers));

$writer->insertOne(createRow([
    'name' => 'Produk B Test', 'slug' => 'produk-b-test', 'description' => 'Deskripsi B', 'price' => 20000, 'stock' => 20, 'weight' => 200, 'is_active' => 1
], $headers));

$writer->insertOne(createRow([
    'name' => 'Produk C Test', 'slug' => 'produk-c-test', 'description' => 'Deskripsi C', 'price' => 30000, 'stock' => 30, 'weight' => 300, 'is_active' => 1
], $headers));

// Baris SALAH (price tidak ada, padahal required berdasarkan error log tadi: "The harga field is required.")
$writer->insertOne(createRow([
    'name' => 'Produk D Error', 'slug' => 'produk-d-error', 'description' => 'Deskripsi D', 'price' => null, 'stock' => 40, 'weight' => 400, 'is_active' => 1
], $headers));

$writer->insertOne(createRow([
    'name' => 'Produk E Test', 'slug' => 'produk-e-test', 'description' => 'Deskripsi E', 'price' => 50000, 'stock' => 50, 'weight' => 500, 'is_active' => 1
], $headers));

echo "CSV berhasil dibuat di storage/app/{$csvPath} dengan 5 baris (1 baris dibuat salah disengaja).\n";
echo "2. Menjalankan Proses Impor via Filament Import...\n";

// Baca CSV
$csvReader = Reader::createFromPath(storage_path('app/' . $csvPath), 'r');
$csvReader->setHeaderOffset(0);
$csvResults = (new Statement)->process($csvReader);
$totalRows = $csvResults->count();

// Siapkan Import Model
$user = User::find(2); // Gunakan Super Admin
$import = new Import();
$import->user()->associate($user);
$import->file_name = 'test_products.csv';
$import->file_path = $csvPath;
$import->importer = ProductImporter::class;
$import->total_rows = $totalRows;
$import->save();

// Buat options kosong & columnMap
$options = [];
$columnMap = array_combine($headers, $headers); // Map 1:1

// Ambil Chunk
$iterator = new \Filament\Support\ChunkIterator($csvResults->getRecords(), chunkSize: 100);
$chunks = $iterator->get();

// Buat import jobs
$jobs = [];
foreach ($chunks as $chunk) {
    $jobs[] = new ImportCsv(
        import: $import,
        rows: base64_encode(serialize($chunk)),
        columnMap: $columnMap,
        options: $options,
    );
}

// Jalankan batch
config(['queue.default' => 'database']);
Bus::batch($jobs)->dispatch();

// Refresh status
$import->refresh();

echo "\n--- HASIL IMPOR PRODUK ---\n";
echo "Total Baris: {$import->total_rows}\n";
echo "Berhasil Diimpor: {$import->successful_rows}\n";
echo "Gagal Diimpor: {$import->failed_rows}\n";

if ($import->failed_rows > 0) {
    echo "\nTerdapat {$import->failed_rows} baris yang gagal diimpor dan diabaikan sesuai harapan (error handling bekerja).\n";
    $failures = $import->failures()->get();
    foreach ($failures as $failure) {
        echo " - Baris {$failure->row}: " . collect($failure->validation_errors)->flatten()->join(', ') . "\n";
    }
}

echo "\nPengetesan Selesai.\n";
