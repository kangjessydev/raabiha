<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detail Atribut')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Atribut')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\Select::make('type')
                            ->label('Tipe Tampilan')
                            ->options([
                                'text' => 'Teks Biasa (Button)',
                                'color' => 'Kotak Warna (Color Swatch)',
                            ])
                            ->required()
                            ->default('text'),
                    ])->columns(3),
                    
                \Filament\Schemas\Components\Section::make('Pilihan Atribut (Options)')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                TextInput::make('value')
                                    ->label('Nilai (Value)')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required(),
                                \Filament\Forms\Components\ColorPicker::make('meta')
                                    ->label('Warna (Khusus Tipe Color)')
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('../../type') === 'color'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Opsi')
                            ->collapsible(),
                    ]),
            ]);
    }
}
