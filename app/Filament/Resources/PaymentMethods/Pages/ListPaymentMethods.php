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
                    $apiKey = env('TRIPAY_API_KEY');
                    $isProduction = env('TRIPAY_MODE', 'sandbox') === 'production';
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
                                
                                \App\Models\PaymentMethod::updateOrCreate(
                                    ['code' => $channel['code']],
                                    [
                                        'name' => $channel['name'],
                                        'description' => 'Grup: ' . $channel['group'],
                                        'logo' => $iconContent ? $iconName : null,
                                        'is_active' => true,
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
