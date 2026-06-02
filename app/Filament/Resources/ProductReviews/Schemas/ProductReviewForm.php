<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Ulasan')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
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
                            ->label('Nama Pengulas')
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('Email Pengulas')
                            ->email()
                            ->required(),
                        TextInput::make('rating')
                            ->label('Rating (1-5)')
                            ->required()
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(5),
                        Toggle::make('is_approved')
                            ->label('Disetujui')
                            ->default(true)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Komentar / Ulasan')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
