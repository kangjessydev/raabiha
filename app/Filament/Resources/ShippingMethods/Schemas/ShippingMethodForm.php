<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

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

                Section::make('Konfigurasi API / Ongkir')
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
