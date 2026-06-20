<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, string $state, Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        RichEditor::make('description')
                            ->label('Deskripsi Produk')
                            ->extraInputAttributes(['style' => 'min-height: 300px;'])
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Placeholder::make('gallery_css')
                            ->hiddenLabel()
                            ->content(new \Illuminate\Support\HtmlString('
                                <style>
                                    /* Hack to make Filament FilePond grid horizontally scrollable with 4 items max */
                                    .horizontal-gallery .filepond--list {
                                        display: flex !important;
                                        flex-wrap: nowrap !important;
                                        overflow-x: auto !important;
                                        gap: 12px;
                                        padding-bottom: 15px;
                                    }
                                    .horizontal-gallery .filepond--item {
                                        width: calc(25% - 9px) !important;
                                        min-width: 150px;
                                        flex: 0 0 auto !important;
                                        position: relative !important;
                                        transform: none !important;
                                        margin: 0 !important;
                                    }
                                </style>
                            ')),
                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('images')
                            ->label('Galeri Produk (Media Library)')
                            ->multiple()
                            ->listDisplay(false)
                            ->buttonLabel('Jelajahi Media Library & Upload Foto')
                            ->color('primary')
                            ->size(\Filament\Support\Enums\Size::Large)
                            ->extraAttributes(['class' => 'horizontal-gallery', 'style' => 'border: 2px dashed #d1d5db; padding: 1.5rem; border-radius: 0.75rem; background-color: #f9fafb; display: flex; flex-direction: column; align-items: center;'])
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Harga & Pengiriman')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga Normal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('discount_price')
                            ->label('Harga Promo (Diskon)')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Isi kolom ini jika ingin menjual dengan harga promo. Harga normal akan dicoret.'),
                        TextInput::make('purchase_price')
                            ->label('Harga Modal (HPP)')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Digunakan untuk menghitung HPP & Laba di Laporan Bisnis. Jika kosong, sistem tidak menghitung modal untuk item ini.'),
                        TextInput::make('reseller_price')
                            ->label('Harga Reseller Khusus')
                            ->helperText('Secara bawaan sistem sudah memotong harga normal sebesar 20% (atau sesuai pengaturan Global Reseller). Isi kolom ini HANYA jika ingin menggunakan harga flat khusus untuk produk ini.')
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
                            ->visible(fn(Get $get) => $get('has_variants') === false)
                            ->required(fn(Get $get) => $get('has_variants') === false),
                        TextInput::make('minimum_stock')
                            ->label('Stok Minimum Peringatan (Tanpa Varian)')
                            ->numeric()
                            ->placeholder('Kosongkan untuk pakai default global (5)')
                            ->visible(fn(Get $get) => $get('has_variants') === false),
                        Toggle::make('has_variants')
                            ->label('Punya Varian?')
                            ->live()
                            ->default(false),
                    ])->columns(2),



                Section::make('Search Engine Optimization (SEO)')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->placeholder('Judul SEO (Maks 60 karakter)'),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->placeholder('Deskripsi singkat untuk pencarian Google (Maks 160 karakter)')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Marketing & Tampilan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Produk Aktif?')
                            ->default(true),
                        Toggle::make('is_hidden')
                            ->label('Sembunyikan dari Katalog (Produk Custom)')
                            ->helperText(function (Get $get) {
                                if ($get('slug')) {
                                    $url = url('/product/' . $get('slug'));
                                    return new \Illuminate\Support\HtmlString('Jika aktif, produk tidak muncul di daftar Shop. <br>Bagikan link ini ke pemesan: <a href="'.$url.'" target="_blank" class="text-primary-600 underline">'.$url.'</a>');
                                }
                                return 'Jika aktif, produk tidak muncul di halaman Shop. Simpan dulu untuk mendapatkan link.';
                            })
                            ->default(false),
                        Toggle::make('has_free_shipping')
                            ->label('Gratis Ongkir')
                            ->default(false),
                    ])->columns(2),

                Section::make('Statistik (Overrides)')
                    ->schema([
                        TextInput::make('rating')
                            ->label('Rating (1.0 - 5.0)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(5)
                            ->default(0.0)
                            ->helperText('Angka desimal, contoh: 4.8'),
                        TextInput::make('sold_count')
                            ->label('Jumlah Terjual (Dummy/Override)')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka penjualan (bisa diisi manual untuk keperluan marketing awal)'),
                    ])->columns(2)->collapsed(),
            ]);
    }
}
