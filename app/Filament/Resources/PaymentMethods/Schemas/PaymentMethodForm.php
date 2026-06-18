<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Metode Pembayaran')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Metode')
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Unik')
                            ->required()
                            ->placeholder('e.g. midtrans, bank_transfer'),
                        Textarea::make('description')
                            ->label('Deskripsi Petunjuk Pembayaran')
                            ->columnSpanFull(),
                        FileUpload::make('logo')
                            ->label('Logo Pembayaran')
                            ->image()
                            ->directory('payment-logos')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        Select::make('availability')
                            ->label('Tampil Di Mana?')
                            ->options([
                                'both' => 'Semua (Online & Offline)',
                                'online' => 'Khusus Website (Online)',
                                'offline' => 'Khusus Kasir (Offline)',
                            ])
                            ->native(false)
                            ->default('both')
                            ->required()
                            ->afterStateHydrated(function (Select $component, $record) {
                                if ($record) {
                                    $config = is_array($record->config) ? $record->config : [];
                                    $component->state($config['availability'] ?? 'both');
                                }
                            }),
                        Select::make('config.group')
                            ->label('Grup Pembayaran')
                            ->options([
                                'Virtual Account' => 'Virtual Account',
                                'E-Wallet' => 'E-Wallet',
                                'QR Code' => 'QR Code',
                                'Retail' => 'Retail (Minimarket)',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->default('Lainnya')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Section::make('Biaya Admin (Dibebankan ke Pelanggan)')
                    ->description('Jika dikosongkan (0), maka biaya transaksi Xendit/Tripay akan dipotong dari saldo Anda (Penjual).')
                    ->schema([
                        TextInput::make('config.fee_customer.flat')
                            ->label('Biaya Admin Flat (Rp)')
                            ->numeric()
                            ->default(0)
                            ->helperText('Biaya tetap. Contoh: Isi 4440 untuk Virtual Account (termasuk PPN 11%).'),
                        TextInput::make('config.fee_customer.percent')
                            ->label('Biaya Admin Persen (%)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->helperText('Biaya persentase. Contoh: Isi 1.5 untuk E-Wallet.'),
                    ])->columns(2),
            ]);
    }
}
