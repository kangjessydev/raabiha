<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Galeri')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul / Nama Foto')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Textarea::make('description')
                            ->label('Deskripsi (Opsional)')
                            ->columnSpanFull(),
                        CuratorPicker::make('image')
                            ->label('Pilih Gambar dari Media Library')
                            ->required()
                            ->color('primary')
                            ->size(\Filament\Support\Enums\Size::Large)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Tampilkan di Website?')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
