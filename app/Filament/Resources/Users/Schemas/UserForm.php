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
                        \Filament\Schemas\Components\Group::make([
                            \Filament\Schemas\Components\View::make('components.avatar-style'),
                            \Awcodes\Curator\Components\Forms\CuratorPicker::make('avatar_url')
                                ->label('Foto Profil')
                                ->buttonLabel('Pilih Foto Profil')
                                ->constrained(true)
                                ->extraAttributes(['class' => 'custom-avatar-picker']),
                        ]),
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText('Kosongkan jika tidak ingin mengubah password.')
                            ->revealable(),
                    ]),

                \Filament\Schemas\Components\Section::make('Hak Akses & Status')
                    ->schema([
                        \Filament\Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Peran (Role Sistem)')
                            ->visible(fn() => auth()->user()->hasRole('super_admin')),
                        \Filament\Forms\Components\Select::make('reseller_status')
                            ->options([
                                'none' => 'Bukan Reseller',
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Reseller Aktif',
                            ])
                            ->default('none'),
                    ])->columns(2)->collapsed(),

                \Filament\Schemas\Components\Section::make('Alamat & Kontak')
                    ->description('Daftar alamat pengiriman dan nomor telepon pelanggan.')
                    ->collapsed()
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('addresses')
                            ->relationship('addresses')
                            ->label('Daftar Alamat')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('title')
                                    ->label('Label Alamat (Contoh: Rumah, Kantor)'),
                                \Filament\Forms\Components\TextInput::make('recipient_name')
                                    ->label('Nama Penerima'),
                                \Filament\Forms\Components\TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel(),
                                \Filament\Forms\Components\TextInput::make('full_address')
                                    ->label('Alamat Lengkap')
                                    ->columnSpanFull(),
                                \Filament\Forms\Components\TextInput::make('province')
                                    ->label('Provinsi'),
                                \Filament\Forms\Components\TextInput::make('city')
                                    ->label('Kota/Kabupaten'),
                                \Filament\Forms\Components\TextInput::make('district')
                                    ->label('Kecamatan'),
                                \Filament\Forms\Components\TextInput::make('postal_code')
                                    ->label('Kode Pos'),
                                \Filament\Forms\Components\Toggle::make('is_primary')
                                    ->label('Jadikan Alamat Utama')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
                    ]),
            ])
            ->columns(1);
    }
}
