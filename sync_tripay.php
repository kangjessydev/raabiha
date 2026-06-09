<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('TRIPAY_API_KEY');
$url = env('TRIPAY_MODE') === 'sandbox' ? 'https://tripay.co.id/api-sandbox/merchant/payment-channel' : 'https://tripay.co.id/api/merchant/payment-channel';

$response = Illuminate\Support\Facades\Http::withToken($apiKey)->get($url);
$data = $response->json('data');

if ($data) {
    foreach ($data as $channel) {
        \App\Models\PaymentMethod::updateOrCreate(
            ['code' => $channel['code']],
            [
                'name' => $channel['name'],
                'description' => 'Pembayaran otomatis via ' . $channel['name'],
                'logo' => $channel['icon_url'],
                'is_active' => false,
                'config' => [
                    'availability' => 'both',
                    'group' => $channel['group'],
                    'fee_merchant' => $channel['fee_merchant']['total'] ?? 0,
                    'fee_customer' => $channel['fee_customer']['total'] ?? 0,
                ]
            ]
        );
    }
    echo "Synced " . count($data) . " channels.\n";
} else {
    echo "Failed to fetch channels.\n";
}
