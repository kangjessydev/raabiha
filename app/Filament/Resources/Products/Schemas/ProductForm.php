<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, string $state, Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        RichEditor::make('description')
                            ->label('Deskripsi Produk')
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->label('Foto Produk (Bisa Pilih Banyak)')
                            ->image()
                            ->multiple()
                            ->directory('products')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Harga & Pengiriman')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga Normal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('reseller_price')
                            ->label('Harga Reseller')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('weight')
                            ->label('Berat')
                            ->required()
                            ->numeric()
                            ->suffix('gram')
                            ->default(1000),
                        TextInput::make('stock')
                            ->label('Stok Produk (Tanpa Varian)')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Get $get) => $get('has_variants') === false)
                            ->required(fn (Get $get) => $get('has_variants') === false),
                        Toggle::make('has_variants')
                            ->label('Punya Varian?')
                            ->live()
                            ->default(false),
                    ])->columns(2),

                Section::make('Daftar Varian Produk')
                    ->visible(fn (Get $get) => $get('has_variants') === true)
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Varian')
                                    ->placeholder('Misal: Hitam - M')
                                    ->required(),
                                TextInput::make('sku')
                                    ->label('SKU (Barcode)')
                                    ->unique(ignoreRecord: true),
                                TextInput::make('stock')
                                    ->label('Stok Varian')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_price_override')
                                            ->label('Gunakan Harga Kustom?')
                                            ->live(),
                                        Toggle::make('is_weight_override')
                                            ->label('Gunakan Berat Kustom?')
                                            ->live(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Harga Normal Varian')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->visible(fn (Get $get) => $get('is_price_override') === true)
                                            ->required(fn (Get $get) => $get('is_price_override') === true),
                                        TextInput::make('reseller_price')
                                            ->label('Harga Reseller Varian')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->visible(fn (Get $get) => $get('is_price_override') === true),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('weight')
                                            ->label('Berat Varian')
                                            ->numeric()
                                            ->suffix('gram')
                                            ->visible(fn (Get $get) => $get('is_weight_override') === true)
                                            ->required(fn (Get $get) => $get('is_weight_override') === true),
                                    ]),

                                Select::make('attributeOptions')
                                    ->label('Kombinasi Pilihan Atribut')
                                    ->multiple()
                                    ->relationship('attributeOptions', 'value')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->attribute->name}: {$record->value}")
                                    ->preload()
                                    ->required(),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Varian')
                            ->collapsible()
                    ]),

                Section::make('Pengaturan Tambahan')
                    ->schema([
                        Textarea::make('wholesale_pricing')
                            ->label('Aturan Harga Grosir (Opsional)'),
                        Textarea::make('promo_rules')
                            ->label('Aturan Promo (Opsional)'),
                        Toggle::make('is_active')
                            ->label('Produk Aktif?')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
