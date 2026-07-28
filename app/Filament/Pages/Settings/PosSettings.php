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

class PosSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-printer';
    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';
    protected static ?string $navigationLabel = 'Pengaturan Kasir (POS)';
    protected static ?string $title = 'Pengaturan Kasir (POS)';
    protected static ?int $navigationSort = 4;
    
    protected string $view = 'filament.clusters.settings.pages.pos-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        if (isset($settings['pos_loyalty_tiers'])) {
            $val = $settings['pos_loyalty_tiers'];
            $settings['pos_loyalty_tiers'] = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
        } else {
            $settings['pos_loyalty_tiers'] = [];
        }
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        $canUpdate = auth()->user()->can('Update:PosSettings');
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->disabled(!$canUpdate)
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Struk Thermal')
                            ->icon('heroicon-o-document-text')
                            ->components([
                                Forms\Components\Toggle::make('pos_receipt_logo_enabled')
                                    ->label('Tampilkan Logo Toko di Struk')
                                    ->default(false),
                                Forms\Components\Textarea::make('pos_receipt_header')
                                    ->label('Header Struk')
                                    ->helperText('Teks di bagian atas struk, misalnya alamat dan nomor telepon. (Maksimal 3-4 baris)')
                                    ->rows(3)
                                    ->default("TOKO RAABIHA\nJl. Contoh No. 123\nTelp: 08123456789"),
                                Forms\Components\Textarea::make('pos_receipt_footer')
                                    ->label('Footer Struk')
                                    ->helperText('Teks di bagian bawah struk, misalnya ucapan terima kasih.')
                                    ->rows(2)
                                    ->default("Terima Kasih\nBarang yang sudah dibeli tidak dapat ditukar/dikembalikan"),
                                Forms\Components\Select::make('pos_paper_size')
                                    ->label('Ukuran Kertas')
                                    ->options([
                                        '58' => '58mm (Printer Portabel)',
                                        '80' => '80mm (Printer Desktop)',
                                    ])
                                    ->default('58')
                                    ->required(),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Perangkat (Hardware)')
                            ->icon('heroicon-o-cpu-chip')
                            ->components([
                                Forms\Components\Toggle::make('pos_auto_cut')
                                    ->label('Otomatis Potong Kertas (Auto-Cut)')
                                    ->helperText('Aktifkan jika printer thermal memiliki fitur pisau otomatis.')
                                    ->default(false),
                                Forms\Components\Toggle::make('pos_open_cash_drawer')
                                    ->label('Otomatis Buka Laci Kasir')
                                    ->helperText('Kirim sinyal untuk membuka laci kasir setiap selesai mencetak struk.')
                                    ->default(false),
                                Forms\Components\Select::make('pos_print_copies')
                                    ->label('Jumlah Cetak')
                                    ->options([
                                        '1' => '1 Lembar (Pelanggan)',
                                        '2' => '2 Lembar (Pelanggan + Arsip Toko)',
                                    ])
                                    ->default('1')
                                    ->required(),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Metadata Struk')
                            ->icon('heroicon-o-information-circle')
                            ->components([
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('pos_ui_logo')
                                    ->label('Logo Aplikasi POS (Tampil di Layar Kasir)')
                                    ->helperText('Logo yang muncul di sudut kiri atas layar POS. Kosongkan untuk menggunakan ikon default.'),
                                Forms\Components\Toggle::make('pos_show_cashier_name')
                                    ->label('Tampilkan Nama Kasir')
                                    ->default(true),
                                Forms\Components\Toggle::make('pos_show_date')
                                    ->label('Tampilkan Tanggal & Jam')
                                    ->default(true),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Loyalti Stempel POS')
                            ->icon('heroicon-o-sparkles')
                            ->components([
                                Forms\Components\Toggle::make('pos_loyalty_enabled')
                                    ->label('Aktifkan Program Loyalti Stempel Digital POS')
                                    ->helperText('🟢 AKTIF: Kasir POS otomatis mengumpulkan stempel & mencetak saldo cap pelanggan di struk thermal. 🔴 NONAKTIF: Seluruh fitur loyalti disembunyikan dari POS & struk.')
                                    ->default(true),
                                Forms\Components\TextInput::make('pos_loyalty_min_spend')
                                    ->label('Minimal Belanja per 1 Stempel (Rp)')
                                    ->helperText('Nominal transaksi minimal untuk mendapatkan 1 stempel (Bebas Anda atur, misal: Rp 10.000, Rp 50.000, atau Rp 100.000).')
                                    ->numeric()
                                    ->default(100000)
                                    ->required(),
                                Forms\Components\TextInput::make('pos_loyalty_stamps_to_points_ratio')
                                    ->label('Rasio Konversi (1 Stempel = Berapa Poin)')
                                    ->helperText('Jumlah poin yang didapatkan dari 1 stempel (Default: 10 Poin per 1 Stempel).')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),
                                Forms\Components\TextInput::make('pos_loyalty_stamp_expiry_months')
                                    ->label('Masa Berlaku Stempel (Bulan)')
                                    ->helperText('Stempel akan hangus jika pelanggan tidak bertransaksi selama X bulan (Default: 6 Bulan, isi 0 jika tanpa kadaluarsa).')
                                    ->numeric()
                                    ->default(6)
                                    ->required(),
                                Forms\Components\Toggle::make('pos_loyalty_multiplier_mode')
                                    ->label('Mode Kelipatan Nominal Belanja')
                                    ->helperText('🟢 AKTIF: Dihitung kelipatan (Cth: Min. Belanja Rp 10.000, Belanja Rp 35.000 = 3 Cap). ⚪ DIMATIKAN: 1 transaksi hanya dapat max 1 Cap berapa pun nominal belanjanya.')
                                    ->default(false),
                                
                                \Filament\Forms\Components\Placeholder::make('loyalty_simulation_notice')
                                    ->label('🧮 Contoh Simulasi Perhitungan Stempel POS')
                                    ->content(new \Illuminate\Support\HtmlString('
                                        <div class="p-4 bg-emerald-50/80 rounded-xl border border-emerald-200 text-emerald-950 text-xs space-y-2">
                                            <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                                                <span>💡 Contoh Kasus Nyata di Kasir POS:</span>
                                            </div>
                                            <p class="leading-relaxed">
                                                Misalkan Anda menetapkan <strong>Minimal Belanja per 1 Stempel = Rp 10.000</strong> & <strong>Rasio = 10 Poin</strong>:
                                            </p>
                                            <ul class="list-disc pl-4 space-y-1">
                                                <li><strong>Jika Mode Kelipatan AKTIF:</strong> Pelanggan belanja <strong>Rp 35.000</strong> &rarr; Otomatis mendapatkan <strong class="text-emerald-700">3 Stempel & 30 Poin</strong>.</li>
                                                <li><strong>Jika Mode Kelipatan DIMATIKAN:</strong> Pelanggan belanja <strong>Rp 35.000</strong> &rarr; Hanya mendapatkan <strong class="text-emerald-700">1 Stempel & 10 Poin</strong>.</li>
                                            </ul>
                                        </div>
                                    ')),

                                \Filament\Forms\Components\Repeater::make('pos_loyalty_tiers')
                                    ->label('🎁 Daftar Tier Hadiah Penukaran Stempel POS')
                                    ->helperText('Pilih Voucher Promo yang sudah Anda buat di menu Voucher, lalu tentukan syarat minimal stempel yang harus dimiliki pelanggan di POS untuk membuka voucher tersebut.')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_stamps')
                                            ->label('Jumlah Stempel Wajib (Syarat)')
                                            ->helperText('Jumlah stempel minimal yang dipotong saat klaim')
                                            ->numeric()
                                            ->required()
                                            ->default(3),
                                        Forms\Components\Select::make('voucher_id')
                                            ->label('Voucher Promo Hadiah')
                                            ->options(fn () => \App\Models\Voucher::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\TextInput::make('description')
                                            ->label('Keterangan Tier Hadiah (Tampil di POS)')
                                            ->placeholder('Contoh: Diskon Member Tier 1 (Potong 3 Cap)')
                                            ->maxLength(255),
                                    ])
                                    ->columns(3)
                                    ->default([]),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Keamanan & Kas Kecil')
                            ->icon('heroicon-o-shield-check')
                            ->components([
                                Forms\Components\Select::make('pos_petty_cash_limit_mode')
                                    ->label('Mode Penghitungan Batas Limit')
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
                                Forms\Components\TextInput::make('pos_refund_max_without_pin')
                                    ->label('Batas Maksimal Refund Tanpa PIN Supervisor (Rp)')
                                    ->helperText('Pengembalian uang tunai / transfer bank yang nilainya sama atau di bawah nominal ini DAPAT diproses langsung oleh kasir tanpa PIN Supervisor. Isi 0 jika INGIN SELURUH REFUND WAJIB PIN Supervisor.')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\Toggle::make('pos_auto_open_drawer_on_petty_cash')
                                    ->label('Otomatis Buka Laci Kasir saat Catat Kas')
                                    ->helperText('Mengirim sinyal elektrik untuk membuka laci kasir secara otomatis begitu kasir menyimpan Kas Masuk / Kas Keluar.')
                                    ->default(true),
                                Forms\Components\Toggle::make('pos_require_pin_for_manual_drawer')
                                    ->label('Wajib PIN Supervisor untuk Buka Laci Manual (No Sale)')
                                    ->helperText('Jika diaktifkan, tombol Buka Laci Manual di POS wajib memasukkan PIN Supervisor demi keamanan laci kasir.')
                                    ->default(true),
                            ]),
                    ])
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
            ->title('Pengaturan POS berhasil disimpan')
            ->success()
            ->send();
    }
}
