<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('TRIPAY_API_KEY');
$privateKey = env('TRIPAY_PRIVATE_KEY');
$merchantCode = env('TRIPAY_MERCHANT_CODE');
$url = 'https://tripay.co.id/api-sandbox/transaction/create';

$merchantRef = 'TEST-12345';
$amount = 10000;
$signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);

$data = [
    'merchant_ref'   => $merchantRef,
    'amount'         => $amount,
    'customer_name'  => 'Test Name',
    'customer_email' => 'test@example.com',
    'customer_phone' => '08123456789',
    'order_items'    => [
        [
            'sku' => 'SKU-1',
            'name' => 'Item 1',
            'price' => 10000,
            'quantity' => 1
        ]
    ],
    'return_url'     => 'https://example.com',
    'expired_time'   => (time() + (24 * 60 * 60)), // 24 hours
    'signature'      => $signature
];

$response = Illuminate\Support\Facades\Http::withToken($apiKey)->post($url, $data);
echo $response->body();
