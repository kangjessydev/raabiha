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
    protected static ?string $navigationLabel = 'Loyalti & Poin';
    protected static ?string $title = 'Pengaturan Poin & Stempel Loyalitas';
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
                Forms\Components\Toggle::make('pos_loyalty_enabled')
                    ->label('Aktifkan Seluruh Program Loyalitas (Poin & Stempel)')
                    ->helperText('Jika dimatikan, semua fitur pengumpulan poin dan stempel akan disembunyikan dari aplikasi kasir.')
                    ->default(true),

                \Filament\Schemas\Components\Section::make('Pengaturan Poin Belanja')
                    ->description('Poin didapatkan dari kelipatan total transaksi dan dapat digunakan sebagai potongan harga.')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(['sm' => 1, 'md' => 3])
                    ->components([
                        Forms\Components\TextInput::make('pos_point_spend_multiplier')
                            ->label('Tiap Kelipatan Belanja (Rp)')
                            ->helperText('Misal: 10000 (Tiap belanja kelipatan sepuluh ribu).')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(10000)
                            ->required(),
                        Forms\Components\TextInput::make('pos_point_earned_per_multiplier')
                            ->label('Mendapatkan Jumlah Poin')
                            ->helperText('Jumlah poin yang diraih per kelipatan (Misal: 1 atau 10).')
                            ->numeric()
                            ->suffix('Poin')
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('pos_point_to_rupiah_value')
                            ->label('1 Poin Setara Rupiah')
                            ->helperText('Nilai tukar 1 poin saat dipakai bayar (Misal: Rp 100).')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(100)
                            ->required(),
                    ]),

                \Filament\Schemas\Components\Section::make('Pengaturan Cap Stempel Digital')
                    ->description('Stempel diberikan berdasar kunjungan berbelanja yang dikaitkan dengan Nomor HP pelanggan. Setiap 12 cap (4 kolom × 3 baris) = 1 Kartu Penuh.')
                    ->icon('heroicon-o-sparkles')
                    ->columns(['sm' => 1, 'md' => 3])
                    ->components([
                        Forms\Components\TextInput::make('pos_loyalty_min_spend_first')
                            ->label('Minimal Belanja Cap Pertama (Rp)')
                            ->helperText('Syarat minimal belanja untuk mendapatkan 1st Cap Stempel (Kartu Baru).')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(150000)
                            ->required(),
                        Forms\Components\TextInput::make('pos_loyalty_min_spend')
                            ->label('Minimal Belanja Cap Selanjutnya (Rp)')
                            ->helperText('Syarat minimal belanja untuk mendapatkan Cap ke-2 s/d ke-12.')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(100000)
                            ->required(),
                        Forms\Components\TextInput::make('pos_loyalty_stamps_to_points_ratio')
                            ->label('1 Stempel Setara Poin')
                            ->helperText('Nilai konversi 1 buah stempel ke poin bonus.')
                            ->numeric()
                            ->suffix('Poin')
                            ->default(10)
                            ->required(),
                        \Filament\Forms\Components\Placeholder::make('loyalty_stamp_rule')
                            ->label('Catatan Aturan Stempel (1/12)')
                            ->columnSpan('full')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="text-sm text-gray-500">
                                    <ul class="list-disc pl-4 space-y-1">
                                        <li>Pelanggan <b>wajib</b> memberikan <b>Nomor HP</b> di kasir. Jika tidak pakai Nomor HP, stempel tidak akan tercetak/masuk.</li>
                                        <li>Cap Pertama membutuhkan minimal belanja <b>Rp 150.000</b>. Cap ke-2 s/d ke-12 membutuhkan minimal belanja <b>Rp 100.000</b> per transaksi.</li>
                                        <li>Pelanggan akan mendapat cap dengan format (X/12). Jika cap sudah mencapai 12, sistem akan me-reset ke 1 dan pelanggan tersebut mendapatkan <b>Badge 1 Kartu Penuh</b>.</li>
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
                            ->helperText('Tentukan jenis hadiah (Voucher Promo atau Hadiah Barang Fisik/Manual) serta syarat minimal stempel yang harus dimiliki pelanggan.')
                            ->schema([
                                Forms\Components\TextInput::make('min_stamps')
                                    ->label('Jumlah Stempel Wajib (Syarat)')
                                    ->helperText('Syarat minimal stempel')
                                    ->numeric()
                                    ->required()
                                    ->default(3),
                                Forms\Components\Toggle::make('is_voucher')
                                    ->label('Gunakan Voucher Promo?')
                                    ->helperText('Aktif = Voucher Promo, Mati = Input Hadiah Manual')
                                    ->default(true)
                                    ->reactive(),
                                Forms\Components\Select::make('voucher_id')
                                    ->label('Voucher Promo Hadiah')
                                    ->options(fn () => \App\Models\Voucher::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(fn ($get) => $get('is_voucher') ?? true)
                                    ->visible(fn ($get) => $get('is_voucher') ?? true),
                                Forms\Components\TextInput::make('description')
                                    ->label('Nama Hadiah / Keterangan Tier')
                                    ->placeholder(fn ($get) => ($get('is_voucher') ?? true) ? 'Contoh: Diskon Member Tier 1 (3 Cap)' : 'Contoh: Gratis 1 Pouch Cantik Raabiha')
                                    ->required(fn ($get) => !($get('is_voucher') ?? true))
                                    ->maxLength(255),
                            ])
                            ->columns(4)
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
            ->title('Pengaturan Poin & Stempel berhasil disimpan')
            ->success()
            ->send();
    }
}
