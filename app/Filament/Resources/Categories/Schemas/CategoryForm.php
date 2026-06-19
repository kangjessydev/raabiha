<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class CategoryForm
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
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('description')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Pengaturan & Media')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Kategori Aktif')
                                            ->default(true)
                                            ->required(),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('image')
                                            ->label('Gambar Kategori')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])->columnSpanFull(),
            ]);
    }
}
