<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class GlobalSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Pengaturan Global';
    protected static ?string $title = 'Pengaturan Global';
    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;
    
    protected string $view = 'filament.clusters.settings.pages.global-settings';

    public ?array $data = [];
    public bool $unlockedTripay = false;
    public bool $unlockedXendit = false;
    public bool $unlockedRajaOngkir = false;

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        
        // Decode JSON arrays for repeaters
        foreach (['navbar_links', 'footer_links', 'footer_shop_links', 'footer_brand_links', 'social_links'] as $jsonKey) {
            if (isset($settings[$jsonKey])) {
                $decoded = json_decode($settings[$jsonKey], true);
                $settings[$jsonKey] = is_array($decoded) ? $decoded : [];
            }
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Identitas Toko')
                            ->components([
                                Forms\Components\TextInput::make('site_name')
                                    ->label('Nama Toko')
                                    ->required(),
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('site_logo_light')
                                    ->label('Logo Terang (Light Mode)'),
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('site_logo_dark')
                                    ->label('Logo Gelap (Dark Mode)'),
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('site_favicon')
                                    ->label('Favicon'),
                                Forms\Components\TextInput::make('home_meta_title')
                                    ->label('SEO: Meta Title Beranda')
                                    ->maxLength(60)
                                    ->helperText('Judul SEO Beranda (Maks 60 karakter) untuk pencarian Google.'),
                                Forms\Components\Textarea::make('home_meta_description')
                                    ->label('SEO: Meta Description Beranda')
                                    ->maxLength(160)
                                    ->helperText('Deskripsi singkat website untuk hasil pencarian Google (Maks 160 karakter).')
                                    ->rows(3),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Mode Libur')
                            ->components([
                                Forms\Components\Toggle::make('store_holiday_mode')
                                    ->label('Aktifkan Mode Libur (Tutup Toko)')
                                    ->helperText('Jika diaktifkan, sebuah spanduk peringatan akan muncul di atas halaman website.')
                                    ->default(false),
                                Forms\Components\Textarea::make('store_holiday_message')
                                    ->label('Pesan Pengumuman Libur')
                                    ->helperText('Contoh: "Toko sedang libur Lebaran. Pesanan akan dikirim mulai tanggal 15 Mei."')
                                    ->default('Mohon maaf, toko kami sedang libur. Semua pesanan yang masuk akan diproses dan dikirim setelah kami kembali beroperasi.')
                                    ->rows(3),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Stok & Inventaris')
                            ->components([
                                Forms\Components\TextInput::make('default_minimum_stock')
                                    ->label('Batas Stok Minimum (Global)')
                                    ->numeric()
                                    ->default(5)
                                    ->helperText('Batas stok minimum untuk seluruh produk sebagai acuan notifikasi peringatan. Jika produk memiliki stok minimum khusus, nilai ini akan di-override.'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Kontak & Sosmed')
                            ->components([
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Nomor Telepon / WhatsApp')
                                    ->prefix('+62'),
                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Email Bantuan')
                                    ->email(),
                                Forms\Components\Textarea::make('contact_address')
                                    ->label('Alamat Toko (Fisik)'),
                                Forms\Components\Select::make('rajaongkir_origin_city')
                                    ->label('Kecamatan Asal Pengiriman (Otomatis untuk Ekspedisi)')
                                    ->helperText('Pastikan Anda mencari dan memilih kecamatan asal toko agar API Ekspedisi (RajaOngkir) bisa mengkalkulasi ongkir. Ketik minimal 3 huruf.')
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        if (strlen($search) < 3) return [];
                                        
                                        $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
                                        if (!$apiKey) return [];
                                        
                                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                                            'key' => $apiKey
                                        ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                                            'search' => $search
                                        ]);
                                        
                                        if ($response->successful()) {
                                            return collect($response->json('data'))->mapWithKeys(function ($item) {
                                                $key = $item['id'] . '::' . $item['label'];
                                                return [$key => $item['label']];
                                            })->toArray();
                                        }
                                        
                                        return [];
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        if (!$value) return null;
                                        if (strpos($value, '::') !== false) {
                                            return explode('::', $value)[1];
                                        }
                                        return 'Kecamatan Terpilih (ID: ' . $value . ')';
                                    })
                                    ->nullable(),
                                Forms\Components\Repeater::make('social_links')
                                    ->label('Daftar Sosial Media')
                                    ->components([
                                        Forms\Components\Select::make('platform')
                                            ->label('Platform')
                                            ->options([
                                                'instagram' => 'Instagram',
                                                'tiktok' => 'TikTok',
                                                'facebook' => 'Facebook',
                                                'twitter' => 'Twitter / X',
                                                'youtube' => 'YouTube',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('url')
                                            ->label('URL / Link Profil')
                                            ->url()
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Navbar')
                            ->components([
                                Forms\Components\Repeater::make('navbar_links')
                                    ->label('Menu Navigasi Utama')
                                    ->components([
                                        Forms\Components\TextInput::make('label')->required(),
                                        Forms\Components\TextInput::make('url')->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Footer')
                            ->components([
                                \Filament\Schemas\Components\Tabs::make('FooterTabs')
                                    ->tabs([
                                        \Filament\Schemas\Components\Tabs\Tab::make('Umum')
                                            ->components([
                                                Forms\Components\Textarea::make('footer_description')
                                                    ->label('Deskripsi Singkat Footer'),
                                                Forms\Components\TextInput::make('footer_copyright')
                                                    ->label('Teks Copyright')
                                                    ->default('© ' . date('Y') . ' Raabiha. All rights reserved.'),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Kolom Menu 1')
                                            ->components([
                                                Forms\Components\TextInput::make('footer_kolom1_title')
                                                    ->label('Judul Kolom 1')
                                                    ->placeholder('Contoh: SHOP'),
                                                Forms\Components\Repeater::make('footer_shop_links')
                                                    ->label('Daftar Menu Kolom 1')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(0),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Kolom Menu 2')
                                            ->components([
                                                Forms\Components\TextInput::make('footer_kolom2_title')
                                                    ->label('Judul Kolom 2')
                                                    ->placeholder('Contoh: BRAND'),
                                                Forms\Components\Repeater::make('footer_brand_links')
                                                    ->label('Daftar Menu Kolom 2')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(0),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Legal & Bawah')
                                            ->components([
                                                Forms\Components\Repeater::make('footer_links')
                                                    ->label('Tautan Footer (Bottom Legal)')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(0),
                                            ]),
                                    ])
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Integrasi & API')
                            ->components([
                                \Filament\Schemas\Components\Section::make('API & Payment Gateway')
                                    ->schema([
                                        Forms\Components\Radio::make('active_payment_gateway')
                                            ->label('Payment Gateway Aktif')
                                            ->options([
                                                'tripay' => 'Tripay',
                                                'xendit' => 'Xendit',
                                            ])
                                            ->default('tripay')
                                            ->inline()
                                            ->live()
                                            ->required()
                                            ->columnSpanFull(),
                                        \Filament\Schemas\Components\Group::make([
                                            Forms\Components\Select::make('tripay_mode')
                                                ->label('Tripay Environment Mode')
                                                ->options([
                                                    'sandbox' => 'Sandbox (Testing)',
                                                    'production' => 'Production (Live)'
                                                ])
                                                ->default('sandbox')
                                                ->required()
                                                ->columnSpanFull(),
                                            Forms\Components\TextInput::make('tripay_merchant_code')
                                                ->label('Tripay Merchant Code')
                                                ->helperText('Kode merchant dari dashboard Tripay (contoh: T11314)')
                                                ->required()
                                                ->columnSpanFull(),
                                            Forms\Components\TextInput::make('tripay_api_key')
                                                ->label('Tripay API Key')
                                                ->password()
                                                ->helperText('API Key dari dashboard Tripay.')
                                                ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                                ->suffixAction(
                                                    \Filament\Actions\Action::make('unlock_tripay')
                                                        ->icon('heroicon-m-lock-closed')
                                                        ->color('danger')
                                                        ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                                        ->requiresConfirmation()
                                                        ->modalHeading('Otorisasi Keamanan Tripay')
                                                        ->modalDescription('Masukkan password akun Anda untuk mengedit kredensial ini.')
                                                        ->form([
                                                            Forms\Components\TextInput::make('password')
                                                                ->label('Password Anda')
                                                                ->password()
                                                                ->required()
                                                                ->currentPassword()
                                                        ])
                                                        ->action(function (\Livewire\Component $livewire) {
                                                            $livewire->unlockedTripay = true;
                                                        })
                                                )
                                                ->columnSpanFull(),
                                            Forms\Components\TextInput::make('tripay_private_key')
                                                ->label('Tripay Private Key')
                                                ->password()
                                                ->helperText('Private Key untuk memverifikasi Webhook.')
                                                ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                                ->columnSpanFull(),
                                        ])->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_payment_gateway') === 'tripay'),
                                        
                                        \Filament\Schemas\Components\Group::make([
                                            Forms\Components\TextInput::make('xendit_secret_key')
                                                ->label('Xendit Secret Key (Live / Test)')
                                                ->password()
                                                ->helperText('API Key dari dashboard Xendit untuk menerima pembayaran otomatis (dimulai dengan xnd_...).')
                                                ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedXendit)
                                                ->suffixAction(
                                                    \Filament\Actions\Action::make('unlock_xendit')
                                                        ->icon('heroicon-m-lock-closed')
                                                        ->color('danger')
                                                        ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedXendit)
                                                        ->requiresConfirmation()
                                                        ->modalHeading('Otorisasi Keamanan Xendit')
                                                        ->modalDescription('Masukkan password akun Anda untuk mengedit API Key ini.')
                                                        ->form([
                                                            Forms\Components\TextInput::make('password')
                                                                ->label('Password Anda')
                                                                ->password()
                                                                ->required()
                                                                ->currentPassword()
                                                        ])
                                                        ->action(function (\Livewire\Component $livewire) {
                                                            $livewire->unlockedXendit = true;
                                                        })
                                                )
                                                ->columnSpanFull(),
                                        ])->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_payment_gateway') === 'xendit'),
                                        Forms\Components\TextInput::make('rajaongkir_api_key')
                                            ->label('API Key Ekspedisi (Komerce / RajaOngkir)')
                                            ->password()
                                            ->helperText('API Key untuk mengecek ongkos kirim otomatis.')
                                            ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedRajaOngkir)
                                            ->suffixAction(
                                                \Filament\Actions\Action::make('unlock_rajaongkir')
                                                    ->icon('heroicon-m-lock-closed')
                                                    ->color('danger')
                                                    ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedRajaOngkir)
                                                    ->requiresConfirmation()
                                                    ->modalHeading('Otorisasi Keamanan RajaOngkir')
                                                    ->modalDescription('Masukkan password akun Anda untuk mengedit API Key ini.')
                                                    ->form([
                                                        Forms\Components\TextInput::make('password')
                                                            ->label('Password Anda')
                                                            ->password()
                                                            ->required()
                                                            ->currentPassword()
                                                    ])
                                                    ->action(function (\Livewire\Component $livewire) {
                                                        $livewire->unlockedRajaOngkir = true;
                                                    })
                                            )
                                            ->columnSpanFull(),
                                    ]),
                                \Filament\Schemas\Components\Section::make('Tracking & Analytics')
                                    ->schema([
                                        Forms\Components\TextInput::make('looker_studio_embed_url')
                                            ->label('URL Embed Looker Studio')
                                            ->placeholder('Contoh: https://lookerstudio.google.com/embed/reporting/...')
                                            ->helperText('URL ini akan ditampilkan di menu Analitik Pengunjung.')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('google_analytics_id')
                                            ->label('ID Google Analytics (GA4)')
                                            ->placeholder('Contoh: G-XXXXXXXXXX'),
                                        Forms\Components\TextInput::make('meta_pixel_id')
                                            ->label('ID Meta Pixel')
                                            ->placeholder('Contoh: 1234567890'),
                                        Forms\Components\TextInput::make('tiktok_pixel_id')
                                            ->label('ID TikTok Pixel')
                                            ->placeholder('Contoh: C123456...'),
                                    ])->columns(3),
                                \Filament\Schemas\Components\Section::make('Custom Scripts')
                                    ->schema([
                                        Forms\Components\Textarea::make('scripts_header')
                                            ->label('Custom Scripts Lainnya (Header)')
                                            ->helperText('Jika ada script tambahan yang wajib ditaruh di <head>. Pastikan menggunakan tag <script>...</script>')
                                            ->rows(4),
                                        Forms\Components\Textarea::make('scripts_footer')
                                            ->label('Custom Scripts Lainnya (Footer)')
                                            ->helperText('Taruh script berat seperti Widget Live Chat di sini agar website tidak lambat.')
                                            ->rows(4),
                                    ])->columns(1),
                            ]),
                    ])
                    ->columnSpanFull()
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
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
