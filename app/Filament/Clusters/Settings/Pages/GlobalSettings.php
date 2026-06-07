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

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        
        // Decode JSON arrays for repeaters
        foreach (['navbar_links', 'footer_links', 'footer_shop_links', 'footer_brand_links', 'social_links', 'contact_subjects'] as $jsonKey) {
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
                                                return [$item['id'] => $item['label']];
                                            })->toArray();
                                        }
                                        
                                        return [];
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        // Try to fetch label by ID, but Komerce API doesn't have a direct /id endpoint,
                                        // so we rely on what is saved in the DB, or we just show the ID.
                                        // Since we don't store the label separately in settings, we can just return a placeholder or try to hit search again.
                                        return $value ? 'Kecamatan Terpilih (ID: ' . $value . ')' : null;
                                    })
                                    ->required(),
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
                                Forms\Components\Repeater::make('contact_subjects')
                                    ->label('Daftar Subjek Pesan (Contact Us)')
                                    ->components([
                                        Forms\Components\TextInput::make('subject')
                                            ->label('Subjek')
                                            ->required(),
                                        Forms\Components\Toggle::make('save_to_db')
                                            ->label('Simpan ke Database?')
                                            ->default(true)
                                            ->helperText('Jika mati, pesan akan langsung dialihkan ke WhatsApp/Email tanpa disimpan.'),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1),
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
                                Forms\Components\Fieldset::make('API & Payment Gateway')
                                    ->schema([
                                        Forms\Components\TextInput::make('xendit_secret_key')
                                            ->label('Xendit Secret Key (Live / Test)')
                                            ->password()
                                            ->helperText('API Key dari dashboard Xendit untuk menerima pembayaran otomatis (dimulai dengan xnd_...).')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('rajaongkir_api_key')
                                            ->label('API Key Ekspedisi (Komerce / RajaOngkir)')
                                            ->password()
                                            ->helperText('API Key untuk mengecek ongkos kirim otomatis.')
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Fieldset::make('Tracking & Analytics')
                                    ->schema([
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
                                Forms\Components\Fieldset::make('Custom Scripts')
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
