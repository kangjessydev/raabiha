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

class PosLoyaltySettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-gift';
    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';
    protected static ?string $navigationLabel = 'Loyalti Stempel';
    protected static ?string $title = 'Pengaturan Program Loyalti Stempel Digital';
    protected static ?int $navigationSort = 7;
    
    protected string $view = 'filament.pages.settings.pos-settings-form';

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
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Ketentuan & Perhitungan Stempel Digital')
                    ->description('Atur syarat minimal belanja dan konversi stempel digital kasir.')
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
                    ]),
                \Filament\Schemas\Components\Section::make('Tier Hadiah & Penukaran Voucher')
                    ->description('Daftar voucher promo yang dapat ditukarkan pelanggan menggunakan saldo stempel.')
                    ->icon('heroicon-o-gift')
                    ->components([
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
            ->title('Pengaturan Program Loyalti Stempel berhasil disimpan')
            ->success()
            ->send();
    }
}
