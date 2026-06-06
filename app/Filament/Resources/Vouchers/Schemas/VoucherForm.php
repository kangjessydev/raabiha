<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Dasar')
                                    ->schema([
                                        TextInput::make('code')
                                            ->label('Kode Voucher')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->columnSpanFull(),
                                        \Filament\Forms\Components\Select::make('discount_type')
                                            ->label('Tipe Diskon')
                                            ->options([
                                                'fixed' => 'Nominal (Rp)',
                                                'percent' => 'Persentase (%)',
                                            ])
                                            ->default('fixed')
                                            ->native(false)
                                            ->live()
                                            ->required(),
                                        TextInput::make('discount_amount')
                                            ->label('Jumlah Diskon')
                                            ->required()
                                            ->numeric()
                                            ->prefix(fn (\Filament\Forms\Get $get) => $get('discount_type') === 'percent' ? null : 'Rp')
                                            ->suffix(fn (\Filament\Forms\Get $get) => $get('discount_type') === 'percent' ? '%' : null),
                                        TextInput::make('min_purchase')
                                            ->label('Minimal Belanja')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp'),
                                        TextInput::make('max_discount')
                                            ->label('Maksimal Diskon')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->helperText('Hanya berlaku untuk tipe persentase')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('discount_type') === 'percent'),
                                        \Filament\Forms\Components\TagsInput::make('specific_users')
                                            ->label('Spesifik Pengguna (Email)')
                                            ->placeholder('Masukkan email pengguna, tekan enter')
                                            ->helperText('Kosongkan jika voucher berlaku untuk semua orang')
                                            ->columnSpanFull(),
                                        \Filament\Forms\Components\Select::make('free_gift_product_id')
                                            ->label('Produk Hadiah Gratis')
                                            ->relationship('freeGiftProduct', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Pilih produk yang akan diberikan secara gratis (opsional)')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Pengaturan & Batasan')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Voucher Aktif')
                                            ->default(true)
                                            ->required(),
                                        TextInput::make('max_uses')
                                            ->label('Kuota Maksimal')
                                            ->numeric()
                                            ->helperText('Kosongkan untuk tanpa batas (unlimited)'),
                                        \Filament\Forms\Components\DateTimePicker::make('starts_at')
                                            ->label('Berlaku Mulai'),
                                        \Filament\Forms\Components\DateTimePicker::make('expires_at')
                                            ->label('Kedaluwarsa Pada'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])->columnSpanFull(),
            ]);
    }
}
