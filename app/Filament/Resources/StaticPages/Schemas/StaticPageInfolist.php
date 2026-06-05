<?php

namespace App\Filament\Resources\StaticPages\Schemas;

use Filament\Schemas\Schema;

class StaticPageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Infolists\Components\TextEntry::make('title')->label('Judul'),
                \Filament\Infolists\Components\TextEntry::make('slug')->label('Slug'),
                \Filament\Infolists\Components\IconEntry::make('is_active')->label('Aktif')->boolean(),
                \Filament\Infolists\Components\TextEntry::make('content')->label('Konten')->html()->columnSpanFull(),
            ]);
    }
}
