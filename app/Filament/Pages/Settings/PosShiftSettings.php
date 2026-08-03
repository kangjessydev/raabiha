<?php

namespace App\Filament\Pages\Settings;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class PosShiftSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';
    protected static ?string $navigationLabel = 'Shift & Akses Kasir';
    protected static ?string $title = 'Pengaturan Master Shift & Akses Kasir';
    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.settings.pos-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        if (isset($settings['pos_master_shifts'])) {
            $val = $settings['pos_master_shifts'];
            $settings['pos_master_shifts'] = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
        } else {
            $settings['pos_master_shifts'] = [];
        }

        if (isset($settings['pos_whitelist_users'])) {
            $val = $settings['pos_whitelist_users'];
            $settings['pos_whitelist_users'] = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
        } else {
            // Migration dari pos_allowed_user_ids jika pos_whitelist_users belum ada
            $oldAllowed = isset($settings['pos_allowed_user_ids']) ? (is_string($settings['pos_allowed_user_ids']) ? json_decode($settings['pos_allowed_user_ids'], true) : $settings['pos_allowed_user_ids']) : [];
            $settings['pos_whitelist_users'] = collect($oldAllowed ?: [])->map(fn($id) => [
                'user_id' => (string) $id,
                'shift_name' => null,
            ])->values()->toArray();
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Pembatasan Jam Operasional & Toleransi Shift')
                    ->description('Atur aturan pembukaan shift kasir dan toleransi jam masuk / lembur.')
                    ->icon('heroicon-o-clock')
                    ->components([
                        Forms\Components\Toggle::make('pos_shift_restriction_enabled')
                            ->label('Aktifkan Pembatasan Jam Operasional & Shift')
                            ->helperText('AKTIF: Buka shift di luar jam operasional / jadwal shift WAJIB memasukkan PIN Supervisor / Kode Izin Remote. DIMATIKAN: Kasir dapat membuka shift kapan saja 24 jam tanpa batasan.')
                            ->default(true),
                        Forms\Components\TextInput::make('pos_shift_early_grace_minutes')
                            ->label('Toleransi Buka Shift Lebih Awal (Menit)')
                            ->helperText('Kasir diperbolehkan Buka Shift tanpa PIN Supervisor X menit sebelum jam shift dimulai (Default: 60 Menit).')
                            ->numeric()
                            ->default(60)
                            ->required(),
                        Forms\Components\TextInput::make('pos_shift_overtime_max_hours')
                            ->label('Batas Maksimal Lembur Shift (Jam)')
                            ->helperText('Toleransi maksimal keterlambatan Tutup Shift / Lembur sebelum sesi ditandai di luar jam (Default: 4 Jam).')
                            ->numeric()
                            ->default(4)
                            ->required(),
                    ]),
                \Filament\Schemas\Components\Section::make('Master Shift & Penugasan Kasir')
                    ->description('Daftar jam kerja shift operasional toko fisik.')
                    ->icon('heroicon-o-user-group')
                    ->components([
                        \Filament\Forms\Components\Repeater::make('pos_master_shifts')
                            ->label('Daftar Master Shift Operasional Toko')
                            ->helperText('Tambahkan daftar shift operasional toko dan tugaskan kasir ke masing-masing shift.')
                            ->reactive()
                            ->schema([
                                Forms\Components\TextInput::make('shift_name')
                                    ->label('Nama Shift')
                                    ->placeholder('Contoh: Shift 1 - Pagi')
                                    ->required(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Jam Masuk')
                                    ->required(),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Jam Selesai')
                                    ->required(),
                                Forms\Components\Select::make('assigned_cashiers')
                                    ->label('Kasir Ditugaskan')
                                    ->options(function () {
                                        return \App\Models\User::role('kasir')->orWhere('role', 'kasir')->get()
                                            ->mapWithKeys(function ($user) {
                                                $roleNames = $user->roles->pluck('name')->implode(', ');
                                                $roleLabel = $roleNames ?: ($user->role ?: 'kasir');
                                                return [$user->id => $user->name . ' (Role: ' . ucfirst($roleLabel) . ')'];
                                            });
                                    })
                                    ->multiple()
                                    ->searchable()
                                    ->helperText('Pilih kasir yang bertugas di shift ini.'),
                            ])
                            ->columns(4)
                            ->default([]),
                    ]),
                \Filament\Schemas\Components\Section::make('Hak Akses & Pengecualian Akun (Whitelist)')
                    ->description('Beri izin akun non-kasir (misal Manager/Super Admin) untuk membuka Terminal POS dan opsi penugasan shift.')
                    ->icon('heroicon-o-key')
                    ->components([
                        \Filament\Forms\Components\Repeater::make('pos_whitelist_users')
                            ->label('Daftar Akun Pengecualian Akses POS (Whitelist POS)')
                            ->helperText('Secara default, rute POS hanya dapat dibuka oleh akun ber-role Kasir. Daftarkan akun non-kasir di sini untuk mengizinkan akses ke Terminal POS.')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Akun Pengguna Non-Kasir')
                                    ->options(function () {
                                        return \App\Models\User::whereDoesntHave('roles', fn($q) => $q->where('name', 'kasir'))
                                            ->where(function ($q) {
                                                $q->whereNull('role')->orWhere('role', '!=', 'kasir');
                                            })
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                $roleNames = $user->roles->pluck('name')->implode(', ');
                                                $roleLabel = $roleNames ?: ($user->role ?: 'tanpa role');
                                                return [(string) $user->id => $user->name . ' (Role: ' . ucfirst($roleLabel) . ')'];
                                            });
                                    })
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('shift_name')
                                    ->label('Master Shift Terikat (Opsional)')
                                    ->options(function ($get) {
                                        $shifts = $get('../../pos_master_shifts') ?: [];
                                        $options = [];
                                        foreach ($shifts as $s) {
                                            if (!empty($s['shift_name'])) {
                                                $options[$s['shift_name']] = $s['shift_name'] . ' (' . ($s['start_time'] ?? '') . ' - ' . ($s['end_time'] ?? '') . ')';
                                            }
                                        }
                                        return $options;
                                    })
                                    ->placeholder('Bebas Akses (Tanpa Batasan Shift)')
                                    ->helperText('Pilih Master Shift untuk mengikat akun ini pada aturan jam operasional & lembur. Biarkan kosong jika akun ini bebas akses 24 jam.'),
                            ])
                            ->columns(2)
                            ->default([]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Ekstrak list ID pengguna untuk backward compatibility pos_allowed_user_ids
        $whitelistUsers = $data['pos_whitelist_users'] ?? [];
        $allowedIds = collect($whitelistUsers)->pluck('user_id')->filter()->map(fn($id) => (string) $id)->values()->toArray();
        $data['pos_allowed_user_ids'] = $allowedIds;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan Master Shift & Akses Kasir berhasil disimpan')
            ->success()
            ->send();
    }
}
