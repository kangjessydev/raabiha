<?php

namespace App\Filament\Resources\Resellers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Reseller')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                        Select::make('reseller_status')
                            ->label('Status Reseller')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'active' => 'Reseller Aktif',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),
                        Select::make('role')
                            ->label('Peran Sistem')
                            ->options([
                                'customer' => 'Customer',
                                'reseller' => 'Reseller',
                                'admin' => 'Admin',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
