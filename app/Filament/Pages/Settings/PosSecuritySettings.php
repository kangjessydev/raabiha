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
                        Forms\Components\TextInput::make('pos_manual_discount_max_percent_without_pin')
                            ->label('Batas Diskon Manual Persen Tanpa PIN Supervisor (%)')
                            ->helperText('Persentase diskon manual maksimal yang bisa diberikan kasir mandiri tanpa otorisasi PIN Supervisor (Default: 20%).')
                            ->numeric()
                            ->default(20)
                            ->required(),
                        Forms\Components\TextInput::make('pos_manual_discount_max_rp_without_pin')
                            ->label('Batas Diskon Manual Nominal Tanpa PIN Supervisor (Rp)')
                            ->helperText('Nominal potongan rupiah diskon manual maksimal yang bisa diberikan kasir mandiri tanpa otorisasi PIN Supervisor (Default: Rp 50.000).')
                            ->numeric()
                            ->default(50000)
                            ->required(),
                        Forms\Components\TextInput::make('pos_refund_max_without_pin')
                            ->label('Batas Maksimal Refund Tanpa PIN Supervisor (Rp)')
                            ->helperText('Pengembalian uang tunai / transfer bank yang nilainya di bawah nominal ini DAPAT diproses langsung oleh kasir tanpa PIN Supervisor. Isi 0 jika INGIN SELURUH REFUND WAJIB PIN Supervisor.')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
                \Filament\Schemas\Components\Section::make('Pengaturan Kas Kecil & Pengeluaran Toko (Petty Cash)')
                    ->description('Batas maksimal pengeluaran kas toko dari laci kasir.')
                    ->icon('heroicon-o-banknotes')
                    ->components([
                        Forms\Components\Select::make('pos_petty_cash_limit_mode')
                            ->label('Mode Penghitungan Batas Limit Kas Kecil')
                            ->options([
                                'cumulative'      => 'Kalkulasi Akumulasi Total Shift (Default)',
                                'per_transaction' => 'Batas Per Transaksi Tunggal',
                                'both'            => 'Kombinasi (Per Transaksi & Akumulasi Shift)',
                            ])
                            ->default('cumulative')
                            ->required()
                            ->helperText('Mode "Kalkulasi Akumulasi" menghitung total seluruh kas keluar shift ini. Jika total melebihi limit, pengeluaran berikutnya wajib PIN Supervisor.'),
                        Forms\Components\TextInput::make('pos_petty_cash_max_limit')
                            ->label('Batas Maksimal Kas Keluar Mandiri (Rp)')
                            ->helperText('Pengeluaran kasir yang melebihi limit ini WAJIB membutuhkan verifikasi PIN Supervisor (Default: Rp 50.000).')
                            ->numeric()
                            ->default(50000)
                            ->required(),
                        Forms\Components\Toggle::make('pos_auto_open_drawer_on_petty_cash')
                            ->label('Otomatis Buka Laci Kasir saat Catat Kas')
                            ->helperText('Mengirim sinyal elektrik untuk membuka laci kasir secara otomatis begitu kasir menyimpan Kas Masuk / Kas Keluar.')
                            ->default(true),
                    ]),
                \Filament\Schemas\Components\Section::make('Keamanan Laci Kasir (Cash Drawer)')
                    ->description('Pengamanan fisik laci kasir di luar transaksi penjualan.')
                    ->icon('heroicon-o-lock-closed')
                    ->components([
                        Forms\Components\Toggle::make('pos_require_pin_for_manual_drawer')
                            ->label('Wajib PIN Supervisor untuk Buka Laci Manual (No Sale)')
                            ->helperText('Jika diaktifkan, tombol Buka Laci Manual di POS wajib memasukkan PIN Supervisor demi keamanan laci kasir.')
                            ->default(true),
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
