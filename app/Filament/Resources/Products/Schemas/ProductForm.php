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
                            ->label('Foto Produk (Media Library)')
                            ->multiple()
                            ->extraAttributes(['class' => 'horizontal-gallery'])
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
