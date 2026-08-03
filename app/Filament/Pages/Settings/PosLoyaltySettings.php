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
                    ->description('Stempel diberikan berdasar kunjungan berbelanja yang dikaitkan dengan Nomor HP pelanggan. Setiap 9 cap = 1 Kartu Penuh.')
                    ->icon('heroicon-o-sparkles')
                    ->columns(['sm' => 1, 'md' => 2])
                    ->components([
                        Forms\Components\TextInput::make('pos_loyalty_min_spend')
                            ->label('Minimal Belanja Untuk Cap Stempel (Rp)')
                            ->helperText('Syarat minimal belanja agar pelanggan mendapat 1 cap stempel. (Wajib menautkan Nomor HP saat di Kasir!).')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(100000)
                            ->required(),
                        Forms\Components\TextInput::make('pos_loyalty_stamps_to_points_ratio')
                            ->label('1 Stempel Setara Poin')
                            ->helperText('Nilai konversi 1 buah stempel ke poin (Admin bebas mengatur nilai ini).')
                            ->numeric()
                            ->suffix('Poin')
                            ->default(10)
                            ->required(),
                        \Filament\Forms\Components\Placeholder::make('loyalty_stamp_rule')
                            ->label('Catatan Aturan Stempel (1/9)')
                            ->columnSpan('full')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="text-sm text-gray-500">
                                    <ul class="list-disc pl-4 space-y-1">
                                        <li>Pelanggan <b>wajib</b> memberikan <b>Nomor HP</b> di kasir. Jika tidak pakai Nomor HP, stempel tidak akan tercetak/masuk.</li>
                                        <li>Pelanggan akan mendapat cap dengan format (X/9). Jika cap sudah mencapai 9, sistem akan me-reset ke 1 dan pelanggan tersebut mendapatkan <b>Badge 1 Kartu</b>.</li>
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
            ->title('Pengaturan Poin & Stempel berhasil disimpan')
            ->success()
            ->send();
    }
}
