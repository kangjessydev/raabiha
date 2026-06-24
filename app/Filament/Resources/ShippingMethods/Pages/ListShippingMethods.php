<?php

namespace App\Filament\Resources\ShippingMethods\Pages;

use App\Filament\Resources\ShippingMethods\ShippingMethodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingMethods extends ListRecords
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('syncRajaOngkir')
                ->label('Tarik Data RajaOngkir')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->action(function () {
                    $couriers = [
                        ['code' => 'jne', 'name' => 'JNE', 'description' => 'Jalur Nugraha Ekakurir'],
                        ['code' => 'jnt', 'name' => 'J&T Express', 'description' => 'J&T Express'],
                        ['code' => 'sicepat', 'name' => 'SiCepat Ekspres', 'description' => 'SiCepat Ekspres'],
                        ['code' => 'pos', 'name' => 'POS Indonesia', 'description' => 'POS Indonesia'],
                        ['code' => 'tiki', 'name' => 'TIKI', 'description' => 'Titipan Kilat'],
                        ['code' => 'ninja', 'name' => 'Ninja Xpress', 'description' => 'Ninja Xpress'],
                        ['code' => 'lion', 'name' => 'Lion Parcel', 'description' => 'Lion Parcel'],
                        ['code' => 'ide', 'name' => 'ID Express', 'description' => 'ID Express'],
                        ['code' => 'anteraja', 'name' => 'AnterAja', 'description' => 'AnterAja'],
                        ['code' => 'wahana', 'name' => 'Wahana', 'description' => 'Wahana Prestasi Logistik'],
                        ['code' => 'sentral', 'name' => 'Sentral Cargo', 'description' => 'Sentral Cargo'],
                        ['code' => 'sap', 'name' => 'SAP Express', 'description' => 'SAP Express'],
                    ];

                    $count = 0;
                    foreach ($couriers as $courier) {
                        $exists = \App\Models\ShippingMethod::where('code', $courier['code'])->exists();
                        if (!$exists) {
                            \App\Models\ShippingMethod::create([
                                'code' => $courier['code'],
                                'name' => $courier['name'],
                                'description' => $courier['description'],
                                'is_active' => false,
                            ]);
                            $count++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil Menarik Data Kurir')
                        ->body($count > 0 ? "Berhasil menambahkan $count kurir RajaOngkir." : 'Semua kurir sudah ada di database.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
