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
        return [
            \Filament\Actions\Action::make('syncTripay')
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
                                // Download icon
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
                }),
            CreateAction::make(),
        ];
    }
}
