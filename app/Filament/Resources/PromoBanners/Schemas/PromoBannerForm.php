<?php

namespace App\Filament\Resources\PromoBanners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromoBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->directory('banners')
                    ->required(),
                TextInput::make('link')
                    ->label('Link URL')
                    ->default(null),
                \Filament\Forms\Components\Select::make('placement')
                    ->label('Penempatan Banner')
                    ->options([
                        'all' => 'Semua Halaman',
                        'home' => 'Halaman Utama (Home)',
                        'catalog' => 'Halaman Katalog / Shop',
                    ])
                    ->default('all')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
