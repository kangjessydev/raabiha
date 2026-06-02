<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Hak Akses & Status')
                    ->schema([
                        \Filament\Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Peran (Role Sistem)'),
                        \Filament\Forms\Components\Select::make('reseller_status')
                            ->options([
                                'none' => 'Bukan Reseller',
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Reseller Aktif',
                            ])
                            ->default('none'),
                        DateTimePicker::make('email_verified_at'),
                    ])->columns(2),
            ]);
    }
}
