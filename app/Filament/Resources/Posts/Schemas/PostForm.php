<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Konten Artikel')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Artikel')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        RichEditor::make('content')
                                            ->label('Isi Artikel')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
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
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Publikasi & Klasifikasi')
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Terbitkan')
                                            ->default(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                                                if ($state) {
                                                    $set('published_at', now());
                                                }
                                            }),
                                        DateTimePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->default(null),
                                        Select::make('post_category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('tags')
                                            ->label('Tag')
                                            ->relationship('tags', 'name')
                                            ->multiple()
                                            ->preload(),
                                        FileUpload::make('image')
                                            ->label('Gambar Sampul')
                                            ->image()
                                            ->directory('blog'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
