<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class AppearanceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Tampilan & Media';
    protected static ?string $title = 'Pengaturan Tampilan & Media';
    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;
    
    protected string $view = 'filament.clusters.settings.pages.global-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        
        foreach (['navbar_links', 'footer_links', 'footer_shop_links', 'footer_brand_links', 'footer_help_links', 'media_allowed_types'] as $jsonKey) {
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
                        \Filament\Schemas\Components\Tabs\Tab::make('Manajemen Media')
                            ->components([
                                Forms\Components\TextInput::make('media_max_size_mb')
                                    ->label('Maksimal Ukuran Upload (MB)')
                                    ->numeric()
                                    ->default(2)
                                    ->helperText('Batas maksimal ukuran file yang boleh diunggah oleh admin.'),
                                Forms\Components\Toggle::make('media_auto_webp')
                                    ->label('Otomatis Konversi ke WEBP & Kompres')
                                    ->default(true)
                                    ->helperText('Sangat disarankan!'),
                                Forms\Components\Select::make('media_allowed_types')
                                    ->label('Format File yang Diizinkan')
                                    ->multiple()
                                    ->options([
                                        'image/jpeg' => 'JPEG/JPG',
                                        'image/png' => 'PNG',
                                        'image/webp' => 'WEBP',
                                        'image/gif' => 'GIF',
                                        'video/mp4' => 'MP4 Video',
                                        'application/pdf' => 'PDF',
                                    ])
                                    ->default(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
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
                                                Forms\Components\Textarea::make('footer_description')->label('Deskripsi Singkat Footer'),
                                                Forms\Components\TextInput::make('footer_copyright')->label('Teks Copyright')->default('© ' . date('Y') . ' Raabiha.'),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Kolom Menu 1')
                                            ->components([
                                                Forms\Components\TextInput::make('footer_kolom1_title')->label('Judul Kolom 1'),
                                                Forms\Components\Repeater::make('footer_shop_links')
                                                    ->label('Daftar Tautan Kolom 1')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])->columns(2)->defaultItems(0),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Kolom Menu 2')
                                            ->components([
                                                Forms\Components\TextInput::make('footer_kolom2_title')->label('Judul Kolom 2'),
                                                Forms\Components\Repeater::make('footer_brand_links')
                                                    ->label('Daftar Tautan Kolom 2')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])->columns(2)->defaultItems(0),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Kolom Menu 3')
                                            ->components([
                                                Forms\Components\TextInput::make('footer_kolom3_title')->label('Judul Kolom 3'),
                                                Forms\Components\Repeater::make('footer_help_links')
                                                    ->label('Daftar Tautan Kolom 3')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])->columns(2)->defaultItems(0),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('Legal & Bawah')
                                            ->components([
                                                Forms\Components\Repeater::make('footer_links')
                                                    ->label('Tautan Footer')
                                                    ->components([
                                                        Forms\Components\TextInput::make('label')->required(),
                                                        Forms\Components\TextInput::make('url')->required(),
                                                    ])->columns(2)->defaultItems(0),
                                            ]),
                                    ])
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
            ->title('Pengaturan Tampilan berhasil disimpan')
            ->success()
            ->send();
    }
}
