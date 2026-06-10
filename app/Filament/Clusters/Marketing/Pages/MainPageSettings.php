<?php

namespace App\Filament\Clusters\Marketing\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Clusters\Marketing\MarketingCluster;

class MainPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = MarketingCluster::class;
    protected static string | \UnitEnum | null $navigationGroup = 'CMS';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string 
    { 
        return 'CMS Halaman Utama'; 
    }

    public function getTitle(): string 
    { 
        return 'CMS Halaman Utama'; 
    }

    protected string $view = 'filament.pages.main-page-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Ambil semua seting halaman dari database
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();

        // Decode JSON arrays for repeaters & builders
        $jsonKeys = [
            'home_marquee_items',
            'about_values',
            'about_timeline',
            'contact_locations',
            'gallery_content',
        ];

        foreach ($jsonKeys as $jsonKey) {
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
                Tabs::make('PageSettings')
                    ->tabs([
                        // Tab Beranda
                        Tab::make('Beranda (Home)')
                            ->icon('heroicon-o-home')
                            ->components([
                                \Filament\Schemas\Components\Section::make('Hero Section')
                                    ->schema([
                                        TextInput::make('home_hero_tag')
                                            ->label('Tagline Hero (Kecil)')
                                            ->default('DROP 02 // 2026'),
                                        TextInput::make('home_hero_title')
                                            ->label('Judul Utama Hero')
                                            ->default('Architectural Modesty'),
                                        Textarea::make('home_hero_subtitle')
                                            ->label('Sub-judul Hero')
                                            ->default('A dialogue between structural precision and urban fluidity. Redefining the modest silhouette for the next generation.')
                                            ->rows(3),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('home_hero_image')
                                            ->label('Gambar Hero Utama (Latar Kanan)'),
                                        TextInput::make('home_hero_button_text')
                                            ->label('Teks Tombol Hero')
                                            ->default('Explore The Drop'),
                                        TextInput::make('home_hero_button_link')
                                            ->label('Link Tombol Hero')
                                            ->default('#'),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make('Running Text (Marquee)')
                                    ->schema([
                                        Repeater::make('home_marquee_items')
                                            ->label('Daftar Teks Berjalan')
                                            ->schema([
                                                TextInput::make('text')
                                                    ->label('Teks')
                                                    ->required(),
                                            ])
                                            ->defaultItems(4)
                                            ->columns(1),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Lookbook Section')
                                    ->schema([
                                        TextInput::make('home_lookbook_tag')
                                            ->label('Tagline Lookbook')
                                            ->default('LOOKBOOK // 04'),
                                        TextInput::make('home_lookbook_title')
                                            ->label('Judul Lookbook')
                                            ->default('Urban Sanctuary'),
                                        Textarea::make('home_lookbook_description')
                                            ->label('Deskripsi Lookbook')
                                            ->default('Discover the pieces that define the modern modest woman. Our SS24 collection blends utility with tradition, creating a sanctuary of style within the urban grid.')
                                            ->rows(3),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('home_lookbook_image')
                                            ->label('Gambar Lookbook'),
                                        TextInput::make('home_lookbook_button_text')
                                            ->label('Teks Tombol Lookbook')
                                            ->default('Read Journal'),
                                        TextInput::make('home_lookbook_button_link')
                                            ->label('Link Tombol Lookbook')
                                            ->default('#'),
                                    ])->columns(2),
                            ]),

                        // Tab Tentang Kami
                        Tab::make('Tentang Kami (About)')
                            ->icon('heroicon-o-information-circle')
                            ->components([
                                \Filament\Schemas\Components\Section::make('Atelier / Filosofi')
                                    ->schema([
                                        TextInput::make('about_atelier_tag')
                                            ->label('Tagline Filosofi')
                                            ->default('Filosofi Brand'),
                                        TextInput::make('about_atelier_title')
                                            ->label('Judul Filosofi')
                                            ->default('Arsitektur dalam Kesopanan.'),
                                        Textarea::make('about_atelier_description')
                                            ->label('Isi Filosofi')
                                            ->default('Raabiha mendefinisikan kembali modest fashion melalui lensa minimalisme struktural. Setiap jahitan adalah komitmen terhadap presisi intelektual dan estetika urban yang mengakar pada nilai-nilai tradisi yang luhur.')
                                            ->rows(4),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('about_atelier_image')
                                            ->label('Gambar Atelier'),
                                        TextInput::make('about_atelier_badge')
                                            ->label('Teks Badge Gambar')
                                            ->default('Atelier Process'),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make('Visi & Misi')
                                    ->schema([
                                        TextInput::make('about_vision_title')
                                            ->label('Judul Visi')
                                            ->default('Menjadi Mercusuar Global untuk Estetika Modest Modern.'),
                                        Textarea::make('about_vision_description')
                                            ->label('Deskripsi Visi')
                                            ->default('Kami melihat masa depan di mana busana santun bukan hanya tentang penutupan, melainkan tentang perayaan keberanian artistik dan integritas struktural.')
                                            ->rows(3),
                                        TextInput::make('about_values_title')
                                            ->label('Judul Nilai Inti')
                                            ->default('Nilai Inti'),
                                        Repeater::make('about_values')
                                            ->label('Daftar Nilai Inti')
                                            ->schema([
                                                TextInput::make('title')->label('Judul Nilai')->required(),
                                                Textarea::make('description')->label('Deskripsi Nilai')->required()->rows(2),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(2),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make('Perjalanan Kami (Timeline)')
                                    ->schema([
                                        TextInput::make('about_timeline_title')
                                            ->label('Judul Timeline')
                                            ->default('Perjalanan Kami'),
                                        TextInput::make('about_timeline_subtitle')
                                            ->label('Sub-judul Timeline')
                                            ->default('Evolusi dari atelier kecil ke pemimpin global.'),
                                        Repeater::make('about_timeline')
                                            ->label('Daftar Perjalanan (Timeline)')
                                            ->schema([
                                                TextInput::make('year')->label('Tahun')->required(),
                                                TextInput::make('title')->label('Judul Milestone')->required(),
                                                Textarea::make('description')->label('Deskripsi Milestone')->required()->rows(2),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(3),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Kutipan (Quote)')
                                    ->schema([
                                        TextInput::make('about_quote_text')
                                            ->label('Teks Kutipan Utama')
                                            ->default('Kesopanan bukan tentang menyembunyikan, tapi tentang mengungkapkan karakter melalui ketenangan.'),
                                    ]),
                            ]),

                        // Tab Kontak
                        Tab::make('Kontak (Contact)')
                            ->icon('heroicon-o-phone')
                            ->components([
                                \Filament\Schemas\Components\Section::make('Hero Section & Deskripsi')
                                    ->schema([
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('contact_hero_image')
                                            ->label('Gambar Hero Kontak'),
                                        TextInput::make('contact_title')
                                            ->label('Judul Halaman Kontak')
                                            ->default('LOCATIONS'),
                                        TextInput::make('contact_subtitle')
                                            ->label('Sub-judul Halaman Kontak')
                                            ->default('RAABIHA | ARCHITECTURAL MODESTY'),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make('Daftar Toko / Lokasi Cabang')
                                    ->schema([
                                        Repeater::make('contact_locations')
                                            ->label('Cabang Toko')
                                            ->schema([
                                                TextInput::make('badge')->label('Badge (Contoh: FLAGSHIP STORE #1)')->required(),
                                                TextInput::make('name')->label('Nama Kota/Cabang')->required(),
                                                Textarea::make('address')->label('Alamat Lengkap')->required()->rows(2),
                                                Textarea::make('hours')->label('Jam Kerja / Jam Operasional')->required()->rows(2),
                                                Textarea::make('contact')->label('Info Kontak (Telepon & Email)')->required()->rows(2),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(2),
                                    ]),
                            ]),

                        // Tab Galeri
                        Tab::make('Galeri (Gallery)')
                            ->icon('heroicon-o-photo')
                            ->components([
                                Builder::make('gallery_content')
                                    ->label('Komponen Halaman Galeri')
                                    ->blocks([
                                        Builder\Block::make('hero')
                                            ->label('Hero / Header')
                                            ->icon('heroicon-m-bars-3-bottom-left')
                                            ->schema([
                                                TextInput::make('pre_title')
                                                    ->label('Pre-Title (Kecil)'),
                                                RichEditor::make('title')
                                                    ->label('Judul Utama (HTML/Rich Text)')
                                                    ->required()
                                                    ->disableToolbarButtons(['attachFiles']),
                                                Textarea::make('description')
                                                    ->label('Deskripsi'),
                                            ]),
                                        Builder\Block::make('masonry_layout')
                                            ->label('Masonry Grid (Layout Statis)')
                                            ->icon('heroicon-m-squares-2x2')
                                            ->schema([
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_1')
                                                    ->label('Gambar 1 (Kiri Besar)')
                                                    ->required(),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_2')
                                                    ->label('Gambar 2 (Kanan Atas)'),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_3')
                                                    ->label('Gambar 3 (Kanan Bawah)'),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_4')
                                                    ->label('Gambar 4 (Tengah Lebar/Landscape)'),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_5')
                                                    ->label('Gambar 5 (Kiri Bawah)'),
                                                TextInput::make('image_5_title')
                                                    ->label('Judul Teks Gambar 5'),
                                                Textarea::make('image_5_desc')
                                                    ->label('Deskripsi Teks Gambar 5'),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_6')
                                                    ->label('Gambar 6 (Tengah Bawah)'),
                                                Textarea::make('quote_text')
                                                    ->label('Teks Kutipan (Quote)'),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image_7')
                                                    ->label('Gambar 7 (Kanan Bawah)'),
                                            ]),
                                        Builder\Block::make('statement')
                                            ->label('Statement / Filosofi')
                                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                                            ->schema([
                                                TextInput::make('pre_title')
                                                    ->label('Pre-Title (Kecil)'),
                                                RichEditor::make('title')
                                                    ->label('Teks Statement Utama')
                                                    ->required()
                                                    ->disableToolbarButtons(['attachFiles']),
                                            ]),
                                        Builder\Block::make('feature_split')
                                            ->label('Feature Split (Teks & Gambar)')
                                            ->icon('heroicon-m-view-columns')
                                            ->schema([
                                                TextInput::make('pre_title')
                                                    ->label('Pre-Title (Kecil)'),
                                                TextInput::make('title')
                                                    ->label('Judul')
                                                    ->required(),
                                                Textarea::make('description')
                                                    ->label('Deskripsi'),
                                                Repeater::make('features')
                                                    ->label('Fitur / Info Detail')
                                                    ->schema([
                                                        TextInput::make('label')->label('Label (Misal: Fabric)'),
                                                        TextInput::make('value')->label('Nilai (Misal: Silk Blend)'),
                                                    ])
                                                    ->columns(2),
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image')
                                                    ->label('Gambar Kanan')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->cloneable()
                            ]),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan Halaman')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Semua konten halaman berhasil diperbarui!')
            ->success()
            ->send();
    }
}
