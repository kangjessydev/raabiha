<?php

namespace App\Filament\Pages\Settings;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Get;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class PosSecuritySettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';
    protected static ?string $navigationLabel = 'Keamanan & Limit Kasir';
    protected static ?string $title = 'Pengaturan Keamanan & Otorisasi POS';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.settings.pos-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        // Set default type if not present
        if (!isset($settings['pos_discount_limit_type'])) {
            $settings['pos_discount_limit_type'] = 'percent';
        }
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Limit Otorisasi Mandiri Kasir (Tanpa PIN Supervisor)')
                    ->description('Batasi transaksi sensitif agar kasir tidak dapat memberikan diskon berlebihan atau refund tanpa izin supervisor.')
                    ->icon('heroicon-o-shield-exclamation')
                    ->components([
                        \Filament\Schemas\Components\Grid::make(['sm' => 1, 'xl' => 2])
                            ->components([
                                \Filament\Schemas\Components\Fieldset::make('Pembatalan Transaksi (Void)')
                                    ->columns(1)
                                    ->components([
                                        Forms\Components\Toggle::make('pos_require_pin_for_void')
                                            ->label('Wajib PIN Supervisor untuk Void Pesanan')
                                            ->helperText('Jika dinonaktifkan, kasir dapat membatalkan pesanan (Void) tanpa otorisasi Supervisor.')
                                            ->default(true),
                                    ]),

                                \Filament\Schemas\Components\Fieldset::make('Batasan Diskon')
                                    ->columns(1)
                                    ->components([
                                        Forms\Components\Toggle::make('pos_require_pin_for_manual_discount')
                                            ->label('Wajib PIN Supervisor untuk Diskon Manual')
                                            ->helperText('Aktifkan untuk mewajibkan PIN Supervisor jika diskon melebihi batas.')
                                            ->default(true)
                                            ->live(),
                                        Forms\Components\Radio::make('pos_discount_limit_type')
                                            ->label('Gunakan Batasan Diskon Berdasarkan:')
                                            ->options([
                                                'percent' => 'Persentase (%)',
                                                'nominal' => 'Nominal (Rp)',
                                            ])
                                            ->inline()
                                            ->default('percent')
                                            ->live(),

                                        Forms\Components\TextInput::make('pos_manual_discount_max_percent_without_pin')
                                            ->label('Batas Diskon (Persen)')
                                            ->helperText('Diskon maksimal oleh kasir tanpa otorisasi PIN (Default: 20).')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(20)
                                            ->required()
                                            ->visible(fn($get) => $get('pos_require_pin_for_manual_discount') && $get('pos_discount_limit_type') === 'percent'),

                                        Forms\Components\TextInput::make('pos_manual_discount_max_rp_without_pin')
                                            ->label('Batas Diskon (Nominal)')
                                            ->helperText('Potongan rupiah maksimal tanpa otorisasi PIN (Default: 50000).')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(50000)
                                            ->required()
                                            ->visible(fn($get) => $get('pos_require_pin_for_manual_discount') && $get('pos_discount_limit_type') === 'nominal'),
                                    ]),

                                \Filament\Schemas\Components\Fieldset::make('Batasan Refund')
                                    ->columns(1)
                                    ->components([
                                        Forms\Components\Toggle::make('pos_require_pin_for_return')
                                            ->label('Wajib PIN Supervisor untuk Retur Uang')
                                            ->helperText('Aktifkan untuk mewajibkan PIN Supervisor jika retur melebihi batas.')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\TextInput::make('pos_refund_max_without_pin')
                                            ->label('Batas Maksimal Refund (Kasir Mandiri)')
                                            ->helperText('Batas uang pengembalian (refund) yang boleh dilakukan kasir mandiri. Isi 0 jika SELURUH pengembalian barang wajib menggunakan PIN Supervisor.')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->visible(fn($get) => $get('pos_require_pin_for_return')),
                                    ]),
                            ]),
                    ]),
                \Filament\Schemas\Components\Grid::make(['sm' => 1, 'xl' => 2])
                    ->components([
                        \Filament\Schemas\Components\Section::make('Kas Kecil & Pengeluaran Toko')
                            ->description('Batas maksimal pengeluaran kas dari laci kasir.')
                            ->icon('heroicon-o-banknotes')
                            ->columnSpan(1)
                            ->components([
                                Forms\Components\Select::make('pos_petty_cash_limit_mode')
                                    ->label('Mode Penghitungan Batas')
                                    ->options([
                                        'cumulative' => 'Total Akumulasi Shift',
                                        'per_transaction' => 'Per Transaksi Tunggal',
                                        'both' => 'Kombinasi Keduanya',
                                    ])
                                    ->default('cumulative')
                                    ->required()
                                    ->helperText("Mode 'Total Akumulasi' menghitung gabungan dari SEMUA kas keluar di shift kasir tersebut.\nMode 'Per Transaksi' menghitung batas untuk tiap lembar pencatatan."),
                                Forms\Components\TextInput::make('pos_petty_cash_max_limit')
                                    ->label('Batas Maksimal Kas Keluar')
                                    ->helperText('Ini adalah batas maksimal uang yang BOLEH dikeluarkan oleh KASIR tanpa izin. Jika pengeluaran lebih dari ini, WAJIB memasukkan PIN Supervisor (Default: 50000).')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(50000)
                                    ->required(),
                                Forms\Components\Toggle::make('pos_auto_open_drawer_on_petty_cash')
                                    ->label('Otomatis Buka Laci Kasir')
                                    ->helperText('Mengirim sinyal elektrik buka laci begitu kasir menyimpan form kas masuk/keluar.')
                                    ->default(true),
                            ]),
                        \Filament\Schemas\Components\Section::make('Keamanan Laci Kasir Fisik')
                            ->description('Pengamanan laci kasir (Cash Drawer) di luar penjualan.')
                            ->icon('heroicon-o-lock-closed')
                            ->columnSpan(1)
                            ->components([
                                Forms\Components\Toggle::make('pos_require_pin_for_manual_drawer')
                                    ->label('Wajib PIN Supervisor untuk Buka Laci Manual (No Sale)')
                                    ->helperText('Jika diaktifkan, tombol Buka Laci Manual di layar POS wajib meminta PIN Supervisor demi mencegah pembukaan laci kasir sembarangan.')
                                    ->default(true),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan Keamanan & Limit Kasir berhasil disimpan')
            ->success()
            ->send();
    }
}
