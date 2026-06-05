<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Image::make(
                            function ($record) {
                                if (!$record->image) {
                                    return 'https://via.placeholder.com/800x400?text=No+Cover';
                                }
                                $media = \Awcodes\Curator\Models\Media::find($record->image);
                                return $media ? Storage::url($media->path) : 'https://via.placeholder.com/800x400?text=No+Cover';
                            },
                            fn ($record) => $record->title
                        )
                        ->imageWidth('100%')
                        ->visible(fn ($record) => $record->image !== null),
                        
                        Text::make(fn ($record) => $record->title)
                            ->size('text-3xl')
                            ->weight('bold')
                            ->color('gray'),
                            
                        Grid::make(3)
                            ->schema([
                                Text::make(fn ($record) => $record->category ? $record->category->name : 'Tanpa Kategori')
                                    ->badge()
                                    ->color('info'),
                                Text::make(fn ($record) => 'Dipublikasikan: ' . ($record->published_at ? $record->published_at->translatedFormat('d M Y') : 'Draft'))
                                    ->color('gray'),
                                Text::make(fn ($record) => 'Oleh: ' . ($record->author ? $record->author->name : 'Sistem'))
                                    ->color('gray'),
                            ]),
                            
                        Html::make(fn ($record) => '<div class="prose max-w-none dark:prose-invert">' . $record->content . '</div>')
                            ->columnSpanFull(),
                            
                        Text::make(function ($record) {
                            if (!$record->tags || $record->tags->isEmpty()) return '';
                            return 'Tags: ' . $record->tags->pluck('name')->map(fn($t) => '#'.$t)->implode(', ');
                        })
                        ->color('gray')
                        ->visible(fn ($record) => $record->tags && $record->tags->isNotEmpty())
                        ->columnSpanFull(),
                    ])
            ]);
    }
}
