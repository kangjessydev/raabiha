<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = SettingsCluster::class;

    public static function getNavigationIcon(): ?string { return 'heroicon-o-adjustments-horizontal'; }
    public static function getNavigationGroup(): ?string { return 'Global'; }
    public static function getNavigationLabel(): string { return 'Pengaturan Website'; }
    public function getTitle(): string { return 'Pengaturan Website'; }

    protected string $view = 'filament.pages.site-settings';

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
                Section::make('Informasi Umum Toko')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nama Toko')
                            ->required(),
                        TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email(),
                        TextInput::make('contact_phone')
                            ->label('No. Telepon / WhatsApp')
                            ->tel(),
                        Textarea::make('site_description')
                            ->label('Deskripsi Singkat')
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat Toko')
                            ->columnSpanFull(),
                        FileUpload::make('site_logo')
                            ->label('Logo Website')
                            ->image()
                            ->directory('settings')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Media Sosial')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url(),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url(),
                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url(),
                    ])->columns(3),

                Section::make('Search Engine Optimization (Beranda)')
                    ->schema([
                        TextInput::make('home_meta_title')
                            ->label('Meta Title Beranda')
                            ->maxLength(60)
                            ->placeholder('Judul SEO Beranda (Maks 60 karakter)'),
                        Textarea::make('home_meta_description')
                            ->label('Meta Description Beranda')
                            ->maxLength(160)
                            ->placeholder('Deskripsi singkat website untuk pencarian Google (Maks 160 karakter)')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Pengaturan Reseller')
                    ->schema([
                        TextInput::make('global_reseller_discount')
                            ->label('Diskon Global Reseller (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('Contoh: 15')
                            ->helperText('Diskon persentase yang berlaku untuk semua produk jika harga khusus reseller (reseller_price) pada produk tidak diisi.'),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Pengaturan website berhasil disimpan!')
            ->success()
            ->send();
    }
}
