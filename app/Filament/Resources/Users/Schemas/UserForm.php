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
                        \Filament\Forms\Components\Toggle::make('is_pos_supervisor')
                            ->label('Jadikan Supervisor POS (Akses Bypass)')
                            ->helperText('Berikan hak akses untuk mengambil alih sesi kasir secara paksa atau validasi aksi darurat.')
                            ->live()
                            ->default(false),
                        \Filament\Forms\Components\TextInput::make('pos_pin')
                            ->label('PIN Supervisor POS (6 Angka)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->length(6)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->helperText('Wajib diisi jika fitur PIN Supervisor aktif. Kosongkan jika tidak ingin mengubah PIN.')
                            ->visible(fn($get) => $get('is_pos_supervisor')),
                    ])->columns(2)->collapsed(),

                \Filament\Schemas\Components\Section::make('Jadwal Shift POS Kasir')
                    ->description('Opsional. Kosongkan untuk menggunakan penugasan Master Shift dari menu Pengaturan Kasir (POS).')
                    ->schema([
                        \Filament\Forms\Components\TimePicker::make('pos_shift_start')
                            ->label('Jam Shift Awal (Jam Masuk)')
                            ->helperText('Diisi jika pengguna memiliki jam shift khusus di luar Master Shift.')
                            ->seconds(false)
                            ->placeholder('08:00'),
                        \Filament\Forms\Components\TimePicker::make('pos_shift_end')
                            ->label('Jam Shift Akhir (Jam Pulang)')
                            ->helperText('Diisi jika pengguna memiliki jam shift khusus di luar Master Shift.')
                            ->seconds(false)
                            ->placeholder('16:00'),
                    ])->columns(2),

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
