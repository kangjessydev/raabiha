<?php

namespace App\Filament\Resources\StaticPages\Schemas;

use Filament\Schemas\Schema;

class StaticPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('title')
                    ->label('Judul Halaman')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                \Filament\Forms\Components\TextInput::make('slug')
                    ->label('URL Slug')
                    ->required()
                    ->unique(\App\Models\StaticPage::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Forms\Components\RichEditor::make('content')
                    ->label('Konten (HTML)')
                    ->required()
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 500px;'])
                    ->disableToolbarButtons(['attachFiles']),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Aktif & Terpublikasi')
                    ->default(true),
                \Filament\Schemas\Components\Section::make('SEO Settings')
                    ->description('Pengaturan Meta Data untuk Mesin Pencari')
                    ->collapsed()
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->placeholder('Optimal: 50-60 karakter'),
                        \Filament\Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(160)
                            ->placeholder('Optimal: 150-160 karakter'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
