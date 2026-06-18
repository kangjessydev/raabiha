<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;
use League\Csv\Writer;
use League\Csv\Statement;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Pastikan antrean pakai database
config(['queue.default' => 'database']);

$user = User::find(2); // Super Admin

if (!is_dir(storage_path('app/imports'))) {
    mkdir(storage_path('app/imports'), 0755, true);
}

function createRow($data, $headers) {
    $row = [];
    foreach ($headers as $header) {
        $row[] = $data[$header] ?? null;
    }
    return $row;
}

function testImporter($importerClass, $fileName, $rowsData) {
    global $user;
    echo "\n-------------------------------------------------\n";
    echo "MENGUJI IMPORTER: " . class_basename($importerClass) . "\n";
    
    $columns = $importerClass::getColumns();
    $headers = array_map(fn($col) => $col->getName(), $columns);
    
    $csvPath = "imports/{$fileName}";
    $writer = Writer::createFromPath(storage_path('app/' . $csvPath), 'w+');
    $writer->insertOne($headers);
    
    foreach ($rowsData as $data) {
        $writer->insertOne(createRow($data, $headers));
    }
    
    $csvReader = Reader::createFromPath(storage_path('app/' . $csvPath), 'r');
    $csvReader->setHeaderOffset(0);
    $csvResults = (new Statement)->process($csvReader);
    $totalRows = $csvResults->count();
    
    $import = new Import();
    $import->user()->associate($user);
    $import->file_name = $fileName;
    $import->file_path = $csvPath;
    $import->importer = $importerClass;
    $import->total_rows = $totalRows;
    $import->save();
    
    $options = [];
    $columnMap = array_combine($headers, $headers);
    
    $iterator = new \Filament\Support\ChunkIterator($csvResults->getRecords(), chunkSize: 100);
    $chunks = $iterator->get();
    
    $jobs = [];
    foreach ($chunks as $chunk) {
        $jobs[] = new ImportCsv(
            import: $import,
            rows: base64_encode(serialize($chunk)),
            columnMap: $columnMap,
            options: $options,
        );
    }
    
    Bus::batch($jobs)->dispatch();
    
    // Jalankan worker untuk menyelesaikan job
    Artisan::call('queue:work', ['--stop-when-empty' => true]);
    
    // Tunggu sedikit dan ambil hasil
    $import->refresh();
    
    echo "File CSV dibuat dengan {$totalRows} baris.\n";
    echo "Hasil dari database setelah diproses queue:\n";
    echo " - Total Baris : {$import->total_rows}\n";
    echo " - Berhasil    : {$import->successful_rows}\n";
    $failedCount = $import->total_rows - $import->successful_rows;
    echo " - Gagal       : {$failedCount}\n";
    
    if ($failedCount > 0) {
        $failures = DB::table('failed_import_rows')->where('import_id', $import->id)->get();
        echo "   Daftar Error:\n";
        foreach ($failures as $f) {
            $errors = json_decode($f->validation_error, true) ?? $f->validation_error;
            if (is_array($errors)) {
                $errors = implode(', ', \Illuminate\Support\Arr::flatten($errors));
            }
            echo "   [Error] " . $errors . "\n";
        }
    }
}

// 1. PRODUCT IMPORTER
testImporter(
    \App\Filament\Imports\ProductImporter::class,
    'test_import_products.csv',
    [
        ['name' => 'Produk A', 'slug' => 'produk-a', 'price' => 10000, 'stock' => 10, 'weight' => 100, 'is_active' => 1, 'has_variants' => 0],
        ['name' => 'Produk B', 'slug' => 'produk-b', 'price' => 20000, 'stock' => 20, 'weight' => 200, 'is_active' => 1, 'has_variants' => 0],
        ['name' => 'Produk C', 'slug' => 'produk-c', 'price' => null, 'stock' => 30, 'weight' => 300, 'is_active' => 1, 'has_variants' => 0], // SALAH (price null)
    ]
);

// 2. ORDER IMPORTER
testImporter(
    \App\Filament\Imports\OrderImporter::class,
    'test_import_orders.csv',
    [
        ['order_number' => 'ORD-TEST-1', 'customer_name' => 'Budi', 'customer_phone' => '08123', 'status' => 'pending', 'payment_status' => 'unpaid', 'shipping_address' => '{}', 'shipping_cost' => 10000, 'total_amount' => 50000, 'discount_amount' => 0],
        ['order_number' => 'ORD-TEST-2', 'customer_name' => 'Andi', 'customer_phone' => '08124', 'status' => 'processing', 'payment_status' => 'paid', 'shipping_address' => '{}', 'shipping_cost' => 15000, 'total_amount' => 60000, 'discount_amount' => 0],
        ['order_number' => 'ORD-TEST-3', 'customer_name' => '', 'customer_phone' => '', 'status' => 'invalid', 'payment_status' => 'unpaid', 'shipping_address' => '{}', 'shipping_cost' => null, 'total_amount' => null, 'discount_amount' => null], // SALAH (mandatory kosong)
    ]
);

// 3. USER IMPORTER
testImporter(
    \App\Filament\Imports\UserImporter::class,
    'test_import_users.csv',
    [
        ['name' => 'Test User 1', 'email' => 'testuser1@example.com', 'password' => 'password123', 'role' => 'customer'],
        ['name' => 'Test User 2', 'email' => 'testuser2@example.com', 'password' => 'password123', 'role' => 'reseller'],
        ['name' => 'Test User 3', 'email' => 'invalid-email', 'password' => '123', 'role' => 'customer'], // SALAH (email salah)
    ]
);

// 4. CATEGORY IMPORTER
testImporter(
    \App\Filament\Imports\CategoryImporter::class,
    'test_import_categories.csv',
    [
        ['name' => 'Cat A', 'slug' => 'cat-a', 'is_active' => 1],
        ['name' => 'Cat B', 'slug' => 'cat-b', 'is_active' => 1],
        ['name' => null, 'slug' => 'cat-c', 'is_active' => 0], // SALAH (name null)
    ]
);

// 5. POST IMPORTER
testImporter(
    \App\Filament\Imports\PostImporter::class,
    'test_import_posts.csv',
    [
        ['title' => 'Post A', 'slug' => 'post-a', 'content' => 'Isi A', 'is_published' => 1, 'published_at' => '2026-06-18 10:00:00'],
        ['title' => 'Post B', 'slug' => 'post-b', 'content' => 'Isi B', 'is_published' => 1, 'published_at' => '2026-06-18 10:00:00'],
        ['title' => null, 'slug' => 'post-c', 'content' => 'Isi C', 'is_published' => 1, 'published_at' => '2026-06-18 10:00:00'], // SALAH
    ]
);

// 6. VOUCHER IMPORTER
testImporter(
    \App\Filament\Imports\VoucherImporter::class,
    'test_import_vouchers.csv',
    [
        ['code' => 'VOUCHER-TEST1', 'discount_type' => 'fixed', 'discount_amount' => 10000, 'is_active' => 1],
        ['code' => 'VOUCHER-TEST2', 'discount_type' => 'percentage', 'discount_amount' => 10, 'is_active' => 1],
        ['code' => null, 'discount_type' => 'invalid', 'discount_amount' => null, 'is_active' => 0], // SALAH
    ]
);

echo "\n-------------------------------------------------\n";
echo "Semua Importer selesai diuji.\n";
