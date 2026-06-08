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
                                'both'    => 'Semua (Online & Offline)',
                                'online'  => 'Khusus Website (Online)',
                                'offline' => 'Khusus Kasir (Offline)',
                            ])
                            ->default('both')
                            ->required()
                            ->afterStateHydrated(function (Select $component, $record) {
                                if ($record) {
                                    $config = is_array($record->config) ? $record->config : [];
                                    $component->state($config['availability'] ?? 'both');
                                }
                            }),
                    ])->columns(2),

                Section::make('Konfigurasi API / Kredensial')
                    ->schema([
                        KeyValue::make('config')
                            ->label('Parameter Konfigurasi (Key-Value)')
                            ->keyLabel('Nama Parameter')
                            ->valueLabel('Nilai')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
