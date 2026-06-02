<?php

namespace App\Filament\Resources\Comments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Komentar')
                    ->schema([
                        Select::make('post_id')
                            ->label('Artikel Blog')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('User Akun (Opsional)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null),
                        TextInput::make('customer_name')
                            ->label('Nama Komentator')
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('Email Komentator')
                            ->email()
                            ->required(),
                        Toggle::make('is_approved')
                            ->label('Disetujui')
                            ->default(true)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Komentar')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
