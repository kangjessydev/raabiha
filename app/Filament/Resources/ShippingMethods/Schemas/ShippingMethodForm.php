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
                                    ->action(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                        $aliases = $get('config.service_aliases') ?? [];
                                        if (!is_array($aliases)) $aliases = [];
                                        
                                        $commonMappings = [
                                            'CTC' => 'Reguler (Lokal)',
                                            'CTCYES' => 'YES (Lokal)',
                                            'JTR' => 'Kargo (Trucking)',
                                            'REG' => 'Reguler',
                                            'YES' => 'Besok Sampai (YES)',
                                            'OKE' => 'Ekonomi (OKE)',
                                            'EZ' => 'Reguler',
                                            'ECO' => 'Ekonomi',
                                            'Pos Reguler' => 'Reguler',
                                            'Paket Kilat Khusus' => 'Kilat',
                                            'Express' => 'Express',
                                            'ONS' => 'Besok Sampai (ONS)'
                                        ];

                                        foreach ($commonMappings as $code => $name) {
                                            if (!array_key_exists($code, $aliases)) {
                                                $aliases[$code] = $name;
                                            }
                                        }
                                        
                                        $set('config.service_aliases', $aliases);
                                    })
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
