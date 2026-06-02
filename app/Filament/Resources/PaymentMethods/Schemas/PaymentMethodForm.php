<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
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
