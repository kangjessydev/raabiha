<?php

namespace App\Filament\Resources\PromoBanners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class PromoBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Konten Banner')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Banner')
                                            ->required(),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('image')
                                            ->label('Banner Image')
                                            ->buttonLabel('Pilih Media / Unggah')
                                            ->color('primary')
                                            ->size(\Filament\Support\Enums\Size::Medium)
                                            ->required(),
                                        TextInput::make('link')
                                            ->label('Link URL')
                                            ->placeholder('Contoh: https://raabiha.com/shop/promo')
                                            ->default(null),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Pengaturan Banner')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Banner Aktif')
                                            ->helperText('Hanya bisa ada 1 banner aktif dalam satu penempatan.')
                                            ->default(true)
                                            ->required(),
                                        \Filament\Forms\Components\Select::make('placement')
                                            ->label('Penempatan Banner')
                                            ->options([
                                                'all' => 'Semua Halaman',
                                                'home' => 'Halaman Utama (Home)',
                                                'catalog' => 'Halaman Katalog / Shop',
                                            ])
                                            ->default('all')
                                            ->native(false)
                                            ->required(),
                                        TextInput::make('sort_order')
                                            ->label('Urutan')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])->columnSpanFull(),
            ]);
    }
}
