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
            \Filament\Actions\Action::make('aturKebijakanBerat')
                ->label('Atur Kebijakan Berat & Kargo')
                ->icon('heroicon-o-scale')
                ->color('info')
                ->fillForm(function () {
                    return [
                        'weight_rounding_method' => \App\Models\SiteSetting::where('key', 'weight_rounding_method')->value('value') ?? 'ceiling',
                        'weight_tolerance_grams' => \App\Models\SiteSetting::where('key', 'weight_tolerance_grams')->value('value') ?? 300,
                        'cargo_min_weight_grams' => \App\Models\SiteSetting::where('key', 'cargo_min_weight_grams')->value('value') ?? 10000,
                        'test_weight' => 1200,
                    ];
                })
                ->action(function (array $data) {
                    \App\Models\SiteSetting::updateOrCreate(['key' => 'weight_rounding_method'], ['value' => $data['weight_rounding_method']]);
                    \App\Models\SiteSetting::updateOrCreate(['key' => 'weight_tolerance_grams'], ['value' => (string) $data['weight_tolerance_grams']]);
                    \App\Models\SiteSetting::updateOrCreate(['key' => 'cargo_min_weight_grams'], ['value' => (string) $data['cargo_min_weight_grams']]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Kebijakan Berat & Kargo berhasil disimpan')
                        ->success()
                        ->send();
                })
                ->form([
                    \Filament\Schemas\Components\Grid::make(2)->schema([
                        \Filament\Forms\Components\Select::make('weight_rounding_method')
                            ->label('Metode Pembulatan Berat')
                            ->options([
                                'ceiling' => 'Pembulatan Murni Ke Atas (Ceiling)',
                                'tolerance' => 'Menggunakan Batas Toleransi Ekspedisi',
                            ])
                            ->live()
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('weight_tolerance_grams')
                            ->label('Batas Toleransi (Gram)')
                            ->numeric()
                            ->suffix('gram')
                            ->helperText('Contoh: jika diisi 300, berat 1.300g dibulatkan ke bawah (1 kg), sedangkan 1.301g dibulatkan ke atas (2 kg).')
                            ->visible(fn ($get) => $get('weight_rounding_method') === 'tolerance')
                            ->required(fn ($get) => $get('weight_rounding_method') === 'tolerance'),
                        \Filament\Forms\Components\TextInput::make('cargo_min_weight_grams')
                            ->label('Batas Minimum Kurir Kargo')
                            ->numeric()
                            ->suffix('gram')
                            ->helperText('Opsi kurir bertipe kargo (seperti JTR/Gokil) hanya muncul jika berat belanjaan minimal sebesar angka ini. Default: 10000g (10 kg).')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                    
                    \Filament\Schemas\Components\Section::make('Kalkulator Simulasi Berat')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('test_weight')
                                ->label('Simulasikan Berat Paket Anda')
                                ->numeric()
                                ->suffix('gram')
                                ->placeholder('Ketik berat paket dalam gram (misal 1200)')
                                ->live(),
                            \Filament\Forms\Components\Placeholder::make('simulation_output')
                                ->label('Hasil Perhitungan Sistem')
                                ->content(function ($get) {
                                    $testWeight = (int) ($get('test_weight') ?? 0);
                                    $method = $get('weight_rounding_method') ?? 'ceiling';
                                    $tolerance = (int) ($get('weight_tolerance_grams') ?? 300);
                                    
                                    if ($testWeight <= 0) {
                                        return 'Masukkan berat simulasi di atas untuk melihat perhitungan.';
                                    }
                                    
                                    // 1. Minimum weight rule
                                    $baseWeight = max(1000, $testWeight);
                                    
                                    // 2. Ceiling rounding calculation
                                    $ceilingWeight = max(1000, (int) ceil($testWeight / 1000) * 1000);
                                    
                                    // 3. Tolerance calculation
                                    $kg = floor($testWeight / 1000);
                                    $remainder = $testWeight % 1000;
                                    if ($remainder > $tolerance) {
                                        $toleranceWeight = ($kg + 1) * 1000;
                                    } else {
                                        $toleranceWeight = max(1, $kg) * 1000;
                                    }
                                    
                                    $html = "<div class='space-y-2 text-sm'>";
                                    $html .= "<div><strong>Berat Asli Paket</strong>: " . number_format($testWeight) . " gram (" . ($testWeight / 1000) . " kg)</div>";
                                    $html .= "<div class='border-t pt-2 mt-2'>";
                                    
                                    if ($method === 'ceiling') {
                                        $html .= "<div class='text-green-600 font-bold'>✓ Metode Terpilih: Pembulatan Murni Ke Atas</div>";
                                        $html .= "<div>Berat Akhir yang Digunakan: <strong>" . number_format($ceilingWeight) . " gram (" . ($ceilingWeight / 1000) . " kg)</strong></div>";
                                        $html .= "<div class='text-gray-500 text-xs mt-1'>Mencegah selisih ongkir akibat berat dus/bubble wrap.</div>";
                                    } else {
                                        $html .= "<div class='text-green-600 font-bold'>✓ Metode Terpilih: Batas Toleransi ({$tolerance} gram)</div>";
                                        $html .= "<div>Berat Akhir yang Digunakan: <strong>" . number_format($toleranceWeight) . " gram (" . ($toleranceWeight / 1000) . " kg)</strong></div>";
                                        $html .= "<div class='text-gray-500 text-xs mt-1'>Lebih adil bagi pembeli jika beratnya hanya lebih sedikit dari 1 kg.</div>";
                                    }
                                    
                                    $html .= "</div><div class='border-t pt-2 mt-2 space-y-1 text-xs text-gray-500'>";
                                    $html .= "<div>• Bandingkan - Pembulatan Murni: " . ($ceilingWeight / 1000) . " kg</div>";
                                    $html .= "<div>• Bandingkan - Toleransi ({$tolerance}g): " . ($toleranceWeight / 1000) . " kg</div>";
                                    $html .= "</div></div>";
                                    
                                    return new \Illuminate\Support\HtmlString($html);
                                })
                        ])
                ])
                ->modalHeading('Atur Kebijakan Berat & Kargo')
                ->modalDescription('Konfigurasi pembulatan berat paket dan batas minimal berat kurir kargo.')
                ->modalSubmitActionLabel('Simpan Kebijakan')
                ->slideOver(),
            CreateAction::make(),
        ];
    }
}
