<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('TRIPAY_API_KEY');
$url = env('TRIPAY_MODE') === 'sandbox' ? 'https://tripay.co.id/api-sandbox/merchant/payment-channel' : 'https://tripay.co.id/api/merchant/payment-channel';

$response = Illuminate\Support\Facades\Http::withToken($apiKey)->get($url);
echo $response->body();
