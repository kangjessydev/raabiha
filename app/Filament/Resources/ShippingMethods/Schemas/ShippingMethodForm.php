<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Metode Pengiriman')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Ekspedisi')
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Unik')
                            ->required()
                            ->placeholder('e.g. jne, jnt, sicepat'),
                        Textarea::make('description')
                            ->label('Deskripsi Petunjuk Pengiriman')
                            ->columnSpanFull(),
                        FileUpload::make('logo')
                            ->label('Logo Ekspedisi')
                            ->image()
                            ->directory('shipping-logos')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Section::make('Filter Layanan (Opsional)')
                    ->description('Secara bawaan, semua layanan pengiriman (REG, YES, Kargo, dll) akan ditampilkan ke pembeli. Jika Anda hanya ingin mengaktifkan layanan tertentu, ketik kode layanannya di bawah ini lalu tekan Enter.')
                    ->schema([
                        \Filament\Forms\Components\TagsInput::make('config.allowed_services')
                            ->label('Layanan yang Diizinkan')
                            ->placeholder('Pilih atau ketik kode layanan...')
                            ->suggestions([
                                'REG', 'YES', 'JTR', 'OKE',
                                'Pos Reguler', 'Paket Kilat Khusus', 'Express',
                                'ECO', 'ONS', 'TDS', 'CTC', 'CTCYES'
                            ])
                            ->helperText(new \Illuminate\Support\HtmlString('
                                <div class="flex flex-col gap-1 mt-1 text-xs text-gray-500">
                                    <p><strong>Contoh Kode Umum:</strong> JNE (REG, YES, JTR, OKE), POS (Pos Reguler, Paket Kilat Khusus), TIKI (REG, ECO, ONS).</p>
                                    <p><em>Tips: Jika layanan baru tidak ada di daftar saran, Anda bisa mencari kodenya dari resi / website cek ongkir resmi ekspedisi, lalu ketik manual di atas dan tekan <strong>Enter</strong>. Kosongkan jika ingin menampilkan semua layanan.</em></p>
                                </div>
                            '))
                            ->columnSpanFull(),
                        
                        KeyValue::make('config.service_aliases')
                            ->label('Ubah Nama Layanan (Alias)')
                            ->keyLabel('Kode Asli dari RajaOngkir (Contoh: CTC)')
                            ->valueLabel('Nama yang Ingin Ditampilkan (Contoh: Reguler Lokal)')
                            ->addActionLabel('Tambah Alias Baru')
                            ->helperText('Gunakan fitur ini jika Anda ingin mengubah nama layanan dari RajaOngkir agar lebih mudah dipahami pembeli. (Misal: CTC diubah menjadi JNE Reguler).')
                            ->hintAction(
                                Action::make('generateAliases')
                                    ->label('Generate Otomatis')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (\Filament\Schemas\Components\Utilities\Get $schemaGet, \Filament\Schemas\Components\Utilities\Set $schemaSet) {
                                        $aliases = $schemaGet('config.service_aliases') ?? [];
                                        if (!is_array($aliases)) $aliases = [];
                                        
                                        $courierCode = strtolower($schemaGet('code') ?? '');
                                        
                                        $courierMappings = [
                                            'jne' => [
                                                'REG' => 'Reguler',
                                                'YES' => 'YES (Yakin Esok Sampai)',
                                                'OKE' => 'OKE (Ongkos Kirim Ekonomis)',
                                                'JTR' => 'Kargo (JTR)',
                                                'JTR<130' => 'Kargo JTR (<130kg)',
                                                'JTR>130' => 'Kargo JTR (>130kg)',
                                                'JTR>200' => 'Kargo JTR (>200kg)',
                                                'CTC' => 'Reguler Lokal',
                                                'CTCYES' => 'YES Lokal',
                                                'SPS' => 'Super Speed (SPS)',
                                            ],
                                            'jnt' => [
                                                'EZ' => 'Reguler (EZ)',
                                                'J&T ECO' => 'Ekonomi',
                                                'J&T Super' => 'Super',
                                                'ECO' => 'Ekonomi',
                                                'SUPER' => 'Super',
                                            ],
                                            'sicepat' => [
                                                'REG' => 'Reguler',
                                                'BEST' => 'BEST (Besok Sampai)',
                                                'GOKIL' => 'Kargo (GOKIL)',
                                                'HALU' => 'HALU (Ekonomi)',
                                                'SIUNTUNG' => 'SIUNTUNG',
                                            ],
                                            'pos' => [
                                                'Pos Reguler' => 'Reguler',
                                                'Paket Kilat Khusus' => 'Kilat Khusus',
                                                'Express' => 'Express',
                                            ],
                                            'anteraja' => [
                                                'REG' => 'Reguler',
                                                'ND' => 'Next Day',
                                                'ECO' => 'Ekonomi',
                                                'CARGO' => 'Kargo',
                                            ],
                                            'tiki' => [
                                                'REG' => 'Reguler',
                                                'ECO' => 'Ekonomi',
                                                'ONS' => 'ONS (Over Night Service)',
                                                'TRC' => 'Kargo (TRC)',
                                            ],
                                            'ninja' => [
                                                'Standard' => 'Standard',
                                                'Fast' => 'Fast',
                                            ],
                                            'lion' => [
                                                'REGPACK' => 'Reguler',
                                                'ONEPACK' => 'Besok Sampai',
                                                'BOSSPACK' => 'BossPack',
                                                'JAGOPACK' => 'JagoPack (Ekonomi)',
                                                'BIGPACK' => 'Kargo',
                                            ],
                                            'ide' => [
                                                'Standard' => 'Standard',
                                                'Lite' => 'Lite',
                                                'Half Day' => 'Half Day',
                                            ],
                                        ];

                                        $mappingsToUse = $courierMappings[$courierCode] ?? [
                                            'REG' => 'Reguler',
                                            'YES' => 'Besok Sampai',
                                            'ECO' => 'Ekonomi',
                                            'EZ' => 'Reguler',
                                        ];

                                        foreach ($mappingsToUse as $kodeLayanan => $namaLayanan) {
                                            if (!array_key_exists($kodeLayanan, $aliases)) {
                                                $aliases[$kodeLayanan] = $namaLayanan;
                                            }
                                        }
                                        
                                        $schemaSet('config.service_aliases', $aliases);
                                    })
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Aturan Tampilan Layanan (Shipping Rules)')
                    ->description('Buat aturan kustom untuk menyembunyikan atau hanya menampilkan layanan tertentu berdasarkan berat belanjaan atau wilayah tujuan. Jika tidak ada aturan kustom yang cocok untuk kurir ini, sistem otomatis menggunakan Aturan Global.')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('config.custom_rules')
                            ->label('Aturan Kustom')
                            ->createItemButtonLabel('Tambah Aturan Baru')
                            ->schema([
                                \Filament\Forms\Components\Select::make('condition')
                                    ->label('Kondisi')
                                    ->options([
                                        'weight_less_than' => 'Berat Kurang Dari (<) Gram',
                                        'weight_greater_than' => 'Berat Lebih Dari (>=) Gram',
                                        'is_local_province' => 'Tujuan Dalam Provinsi',
                                        'is_outside_province' => 'Tujuan Luar Provinsi',
                                    ])
                                    ->required()
                                    ->live()
                                    ->native(false),
                                \Filament\Forms\Components\TextInput::make('value')
                                    ->label('Nilai Parameter (Gram)')
                                    ->numeric()
                                    ->required(fn ($get) => in_array($get('condition'), ['weight_less_than', 'weight_greater_than']))
                                    ->hidden(fn ($get) => !in_array($get('condition'), ['weight_less_than', 'weight_greater_than'])),
                                \Filament\Forms\Components\Select::make('action')
                                    ->label('Aksi')
                                    ->options([
                                        'hide' => 'Sembunyikan Layanan',
                                        'allow_only' => 'Hanya Tampilkan Layanan',
                                    ])
                                    ->required()
                                    ->native(false),
                                \Filament\Forms\Components\TagsInput::make('services')
                                    ->label('Kode Layanan')
                                    ->placeholder('Ketik kode layanan (misal: JTR, REG) lalu tekan Enter')
                                    ->required()
                                    ->helperText('Gunakan kode asli dari RajaOngkir (seperti REG, YES, JTR, ND, DLL).')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
