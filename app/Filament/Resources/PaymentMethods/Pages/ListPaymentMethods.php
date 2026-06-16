<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        $activeGateway = \App\Models\SiteSetting::where('key', 'active_payment_gateway')->value('value') ?: 'tripay';

        $actions = [];

        if ($activeGateway === 'tripay') {
            $actions[] = \Filament\Actions\Action::make('syncTripay')
                ->label('Tarik Data Tripay')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function () {
                    $apiKey = \App\Models\SiteSetting::where('key', 'tripay_api_key')->value('value') ?: env('TRIPAY_API_KEY');
                    $isProduction = (\App\Models\SiteSetting::where('key', 'tripay_mode')->value('value') ?: env('TRIPAY_MODE', 'sandbox')) === 'production';
                    
                    if (!$apiKey) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menarik data')
                            ->body('API Key Tripay belum dikonfigurasi di Pengaturan Global.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $endpoint = $isProduction 
                        ? 'https://tripay.co.id/api/merchant/payment-channel' 
                        : 'https://tripay.co.id/api-sandbox/merchant/payment-channel';
                        
                    $response = \Illuminate\Support\Facades\Http::withToken($apiKey)->get($endpoint);
                    
                    if ($response->successful() && $response->json('success')) {
                        $channels = $response->json('data');
                        
                        foreach ($channels as $channel) {
                            if ($channel['active']) {
                                $iconUrl = $channel['icon_url'];
                                $iconContent = @file_get_contents($iconUrl);
                                $iconName = 'payment-logos/' . strtolower($channel['code']) . '.png';
                                
                                if ($iconContent) {
                                    \Illuminate\Support\Facades\Storage::disk('public')->put($iconName, $iconContent);
                                }

                                $config = [
                                    'availability' => 'both',
                                    'group'        => $channel['group'],
                                    'fee_merchant' => $channel['fee_merchant'] ?? ['flat' => 0, 'percent' => 0],
                                    'fee_customer' => $channel['fee_customer'] ?? ['flat' => 0, 'percent' => 0],
                                ];
                                
                                \App\Models\PaymentMethod::updateOrCreate(
                                    ['code' => $channel['code']],
                                    [
                                        'name'        => $channel['name'],
                                        'description' => 'Grup: ' . $channel['group'],
                                        'logo'        => $iconContent ? $iconName : null,
                                        'is_active'   => false,
                                        'config'      => $config,
                                    ]
                                );
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil Menarik Data Tripay')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menarik data dari Tripay')
                            ->danger()
                            ->send();
                    }
                });
        } elseif ($activeGateway === 'xendit') {
            $actions[] = \Filament\Actions\Action::make('syncXendit')
                ->label('Tarik Data Xendit')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function () {
                    $apiKey = \App\Models\SiteSetting::where('key', 'xendit_secret_key')->value('value') ?: env('XENDIT_SECRET_KEY');
                    
                    if (!$apiKey) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menarik data')
                            ->body('API Key Xendit belum dikonfigurasi di Pengaturan Global.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Nonaktifkan/hapus payment method sebelumnya (misal punya Tripay) kecuali Tunai/COD
                    \App\Models\PaymentMethod::whereNotIn('code', ['tunai', 'cod'])->delete();

                    // Buat preset channel Xendit agar mirip Tripay
                    $xenditChannels = [
                        ['code' => 'BCA', 'name' => 'BCA Virtual Account', 'group' => 'Virtual Account', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/BCA.png'],
                        ['code' => 'BNI', 'name' => 'BNI Virtual Account', 'group' => 'Virtual Account', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/BNI.png'],
                        ['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'group' => 'Virtual Account', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/Mandiri.png'],
                        ['code' => 'BRI', 'name' => 'BRI Virtual Account', 'group' => 'Virtual Account', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/BRI.png'],
                        ['code' => 'PERMATA', 'name' => 'Permata Virtual Account', 'group' => 'Virtual Account', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/Permata.png'],
                        ['code' => 'OVO', 'name' => 'OVO', 'group' => 'E-Wallet', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/OVO.png'],
                        ['code' => 'DANA', 'name' => 'DANA', 'group' => 'E-Wallet', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/DANA.png'],
                        ['code' => 'LINKAJA', 'name' => 'LinkAja', 'group' => 'E-Wallet', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/LinkAja.png'],
                        ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'group' => 'E-Wallet', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/ShopeePay.png'],
                        ['code' => 'QRIS', 'name' => 'QRIS', 'group' => 'QR Code', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/QRIS.png'],
                        ['code' => 'ALFAMART', 'name' => 'Alfamart', 'group' => 'Retail', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/Alfamart.png'],
                        ['code' => 'INDOMARET', 'name' => 'Indomaret', 'group' => 'Retail', 'logo' => 'https://xendit.co/wp-content/uploads/2021/05/Indomaret.png'],
                    ];

                    foreach ($xenditChannels as $ch) {
                        \App\Models\PaymentMethod::create([
                            'code' => $ch['code'],
                            'name' => $ch['name'],
                            'description' => 'Grup: ' . $ch['group'],
                            'logo' => null, // Kita kosongkan dulu agar tidak error CORS, user bisa ubah sendiri
                            'is_active' => true,
                            'config' => [
                                'availability' => 'both',
                                'group' => $ch['group'],
                                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                                'fee_customer' => ['flat' => 0, 'percent' => 0],
                            ],
                        ]);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil menyiapkan jalur Xendit')
                        ->body('Channel Pembayaran Xendit telah ditambahkan dan diaktifkan.')
                        ->success()
                        ->send();
                });
        }

        $actions[] = CreateAction::make();

        return $actions;
    }
}
