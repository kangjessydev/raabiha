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
            \Filament\Actions\Action::make('syncCouriers')
                ->label('Tarik Data Kurir')
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
                        ->body($count > 0 ? "Berhasil menambahkan $count kurir ke database." : 'Semua kurir sudah ada di database.')
                        ->success()
                        ->send();
                }),
            \Filament\Actions\Action::make('aturTarifManual')
                ->label('Atur Tarif Manual')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->fillForm(function () {
                    $saved = \App\Models\SiteSetting::where('key', 'manual_shipping_rules')->value('value');
                    return [
                        'manual_shipping_rules' => $saved ? json_decode($saved, true) : []
                    ];
                })
                ->action(function (array $data) {
                    \App\Models\SiteSetting::updateOrCreate(
                        ['key' => 'manual_shipping_rules'],
                        ['value' => json_encode($data['manual_shipping_rules'])]
                    );
                    \Filament\Notifications\Notification::make()
                        ->title('Aturan Tarif Manual berhasil disimpan')
                        ->success()
                        ->send();
                })
                ->form([
                    \Filament\Forms\Components\Repeater::make('manual_shipping_rules')
                        ->label('Aturan Tarif Pengiriman')
                        ->addActionLabel('Tambah Aturan Baru')
                        ->reorderableWithButtons()
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->label('Nama Aturan (Misal: Tarif Jabar)')
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('courier')
                                ->label('Nama Kurir / Layanan')
                                ->placeholder('Misal: JNE, Kurir Toko')
                                ->datalist(function () {
                                    return \App\Models\ShippingMethod::where('is_active', true)->pluck('name')->toArray();
                                })
                                ->required(),
                            \Filament\Forms\Components\Select::make('scope')
                                ->label('Cakupan Wilayah')
                                ->options([
                                    'province' => 'Satu Provinsi Spesifik',
                                    'island' => 'Satu Pulau (Semua Provinsi di Dalamnya)',
                                    'national' => 'Default (Berlaku untuk semua wilayah lainnya)',
                                ])
                                ->live()
                                ->native(false)
                                ->required(),
                            \Filament\Forms\Components\Select::make('island_name')
                                ->label('Pilih Pulau')
                                ->options([
                                    'jawa' => 'Pulau Jawa',
                                    'sumatera' => 'Pulau Sumatera',
                                    'kalimantan' => 'Pulau Kalimantan',
                                    'sulawesi' => 'Pulau Sulawesi',
                                    'bali_nt' => 'Bali & Nusa Tenggara',
                                    'maluku_papua' => 'Maluku & Papua',
                                ])
                                ->visible(fn ($get) => $get('scope') === 'island')
                                ->required(fn ($get) => $get('scope') === 'island'),
                            \Filament\Forms\Components\TextInput::make('province_name')
                                ->label('Nama Provinsi')
                                ->helperText('Ketik nama provinsi sesuai dengan data Emsifa (misal: JAWA BARAT).')
                                ->visible(fn ($get) => $get('scope') === 'province')
                                ->required(fn ($get) => $get('scope') === 'province'),
                            \Filament\Forms\Components\TextInput::make('rate')
                                ->label('Tarif Flat (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Placeholder::make('info')
                        ->content(new \Illuminate\Support\HtmlString('
                            <strong>Penting:</strong> Sistem akan membaca aturan dari urutan paling atas ke bawah. 
                            Pastikan aturan yang spesifik (seperti Provinsi) berada di atas aturan yang luas (seperti Pulau atau Default).
                            Gunakan tombol panah di kanan untuk mengatur urutannya.
                        '))
                        ->columnSpanFull(),
                ])
                ->modalHeading('Atur Tarif Manual (Emsifa)')
                ->modalDescription('Konfigurasi ini akan ditambahkan sebagai pilihan kurir saat Checkout (menggunakan deteksi provinsi Emsifa).')
                ->modalSubmitActionLabel('Simpan Pengaturan')
                ->slideOver(),
            CreateAction::make(),
        ];
    }
}
