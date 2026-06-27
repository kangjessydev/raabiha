<?php

namespace App\Filament\Clusters\Settings\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class GlobalSettings extends Page implements HasForms
{
    use HasPageShield;

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
        foreach (['navbar_links', 'footer_links', 'footer_shop_links', 'footer_brand_links', 'footer_help_links', 'social_links', 'media_allowed_types'] as $jsonKey) {
            if (isset($settings[$jsonKey])) {
                $decoded = json_decode($settings[$jsonKey], true);
                $settings[$jsonKey] = is_array($decoded) ? $decoded : [];
            }
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        $canUpdate = auth()->user()->can('Update:GlobalSettings');
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->disabled(!$canUpdate)
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
                                    ->visible(fn () => \App\Models\SiteSetting::where('key', 'active_shipping_provider')->value('value') !== 'binderbyte')
                                    ->nullable(),

                                Forms\Components\Select::make('binderbyte_origin_province')
                                    ->label('Provinsi Asal Toko (BinderByte)')
                                    ->options(function () {
                                        return collect(\App\Services\BinderByteService::getProvinces())->pluck('name', 'id')->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('binderbyte_origin_city', null) ?? $set('binderbyte_origin_district', null))
                                    ->visible(fn () => \App\Models\SiteSetting::where('key', 'active_shipping_provider')->value('value') === 'binderbyte')
                                    ->nullable(),

                                Forms\Components\Select::make('binderbyte_origin_city')
                                    ->label('Kabupaten/Kota Asal Toko (BinderByte)')
                                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                        $provId = $get('binderbyte_origin_province');
                                        if (!$provId) return [];
                                        return collect(\App\Services\BinderByteService::getCities($provId))->pluck('name', 'id')->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('binderbyte_origin_district', null))
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => \App\Models\SiteSetting::where('key', 'active_shipping_provider')->value('value') === 'binderbyte' && !empty($get('binderbyte_origin_province')))
                                    ->nullable(),

                                Forms\Components\Select::make('binderbyte_origin_district')
                                    ->label('Kecamatan Asal Toko (BinderByte)')
                                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                        $cityId = $get('binderbyte_origin_city');
                                        if (!$cityId) return [];
                                        return collect(\App\Services\BinderByteService::getDistricts($cityId))->pluck('name', 'id')->toArray();
                                    })
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => \App\Models\SiteSetting::where('key', 'active_shipping_provider')->value('value') === 'binderbyte' && !empty($get('binderbyte_origin_city')))
                                    ->nullable(),

                                Forms\Components\Repeater::make('social_links')
                                    ->label('Daftar Sosial Media')
                                    ->components([
                                        Forms\Components\TextInput::make('platform')
                                            ->label('Nama Platform (Cth: Shopee, Pinterest)')
                                            ->required(),
                                        Forms\Components\TextInput::make('url')
                                            ->label('URL / Link Profil')
                                            ->url()
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Mode Libur')
                            ->components([
                                Forms\Components\Toggle::make('store_holiday_mode')
                                    ->label('Aktifkan Mode Libur (Tutup Toko)')
                                    ->helperText('Jika diaktifkan, sebuah spanduk peringatan akan muncul di atas halaman website.')
                                    ->default(false),
                                Forms\Components\Textarea::make('store_holiday_message')
                                    ->label('Pesan Pengumuman Libur')
                                    ->helperText('Contoh: "Toko sedang libur Lebaran."')
                                    ->default('Mohon maaf, toko kami sedang libur. Semua pesanan yang masuk akan diproses dan dikirim setelah kami kembali beroperasi.')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        abort_unless(auth()->user()->can('Update:GlobalSettings'), 403);
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
