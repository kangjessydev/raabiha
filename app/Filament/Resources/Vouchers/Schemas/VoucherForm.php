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
                                        TextInput::make('name')
                                            ->label('Nama Voucher')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('code')
                                            ->label('Kode Voucher')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        \Filament\Forms\Components\Radio::make('discount_type')
                                            ->label('Tipe Diskon')
                                            ->options([
                                                'fixed' => 'Nominal (Rp)',
                                                'percent' => 'Persentase (%)',
                                            ])
                                            ->default('fixed')
                                            ->inline()
                                            ->live()
                                            ->required(),
                                        TextInput::make('discount_amount')
                                            ->label('Jumlah Diskon')
                                            ->required()
                                            ->numeric()
                                            ->prefix(fn ($get) => $get('discount_type') === 'percent' ? null : 'Rp')
                                            ->suffix(fn ($get) => $get('discount_type') === 'percent' ? '%' : null),
                                        Toggle::make('is_shipping_voucher')
                                            ->label('Ini adalah Voucher Potongan Ongkir')
                                            ->helperText('Jika aktif, diskon akan memotong biaya pengiriman, bukan total produk. Bisa digabung dengan diskon produk.')
                                            ->columnSpanFull(),
                                        TextInput::make('min_purchase')
                                            ->label('Minimal Belanja')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp'),
                                        TextInput::make('min_items')
                                            ->label('Minimal Jumlah Item')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Minimal qty barang di keranjang'),
                                        TextInput::make('max_discount')
                                            ->label('Maksimal Diskon')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->helperText('Hanya berlaku untuk tipe persentase')
                                            ->visible(fn ($get) => $get('discount_type') === 'percent'),
                                        \Filament\Forms\Components\Select::make('specific_users')
                                            ->label('Spesifik Pengguna (Email)')
                                            ->multiple()
                                            ->searchable()
                                            ->getSearchResultsUsing(fn (string $search): array => \App\Models\User::where('email', 'like', "%{$search}%")->limit(50)->pluck('email', 'email')->toArray())
                                            ->getOptionLabelsUsing(fn (array $values): array => \App\Models\User::whereIn('email', $values)->pluck('email', 'email')->toArray())
                                            ->placeholder('Cari email pengguna...')
                                            ->helperText('Kosongkan jika voucher berlaku untuk semua orang')
                                            ->columnSpanFull(),
                                        Toggle::make('exclude_resellers')
                                            ->label('Larang Reseller Menggunakan Voucher Ini')
                                            ->default(true)
                                            ->helperText('Direkomendasikan Aktif: karena reseller sudah mendapat potongan harga grosir otomatis.')
                                            ->columnSpanFull(),
                                        Toggle::make('is_stackable')
                                            ->label('Izinkan Gabung (Stackable) dengan Voucher Lain')
                                            ->default(false)
                                            ->helperText('Jika aktif, voucher ini bisa digunakan bersamaan dengan voucher lain yang juga aktif stackable-nya.')
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
