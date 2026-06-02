<?php

namespace App\Filament\Resources\Salespages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SalespageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Kode Kustom Halaman')
                                    ->schema([
                                        Textarea::make('html_content')
                                            ->label('HTML Content')
                                            ->rows(15)
                                            ->placeholder('<div>...</div>')
                                            ->columnSpanFull(),
                                        Textarea::make('css_content')
                                            ->label('CSS Content')
                                            ->rows(10)
                                            ->placeholder('body { ... }')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi & Status')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Sales Page')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->label('Slug Halaman')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
