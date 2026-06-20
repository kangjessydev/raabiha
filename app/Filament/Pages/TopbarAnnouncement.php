<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Models\TopbarAnnouncement as AnnouncementModel;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class TopbarAnnouncement extends Page implements HasForms
{
    use HasPageShield;

    use InteractsWithForms;

    protected static ?string $cluster = ECommerceCluster::class;
                    
    public static function getNavigationIcon(): ?string { return 'heroicon-o-megaphone'; }
    public static function getNavigationGroup(): string|\BackedEnum|null { return \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Promosi; }
    public static function getNavigationLabel(): string { return 'Pengumuman (Topbar)'; }
    public function getTitle(): string { return 'Pengumuman Topbar'; }

    protected string $view = 'filament.pages.topbar-announcement';

    public ?array $data = [];

    public function mount(): void
    {
        $announcement = AnnouncementModel::first();
        if ($announcement) {
            $this->data = $announcement->toArray();
        } else {
            $this->data = [
                'text' => 'Diskon Spesial Hari Ini!',
                'link' => null,
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
                'is_active' => true,
            ];
        }
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konfigurasi Bilah Pengumuman')
                    ->schema([
                        \Filament\Forms\Components\RichEditor::make('text')
                            ->label('Teks Pengumuman')
                            ->placeholder('e.g. Dapatkan gratis ongkir dengan minimum pembelian Rp 150.000!')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                            ])
                            ->live(debounce: 500)
                            ->helperText(function ($state) {
                                $text = strip_tags((string) $state);
                                $text = str_replace('&nbsp;', ' ', $text);
                                $count = mb_strlen(html_entity_decode(trim($text)));
                                
                                if ($count > 100) {
                                    return new \Illuminate\Support\HtmlString('<span style="color:#eab308; font-weight:bold;">Karakter: ' . $count . '/100. Karena lebih dari 100 karakter, teks akan otomatis menjadi efek berjalan (Marquee) di layar HP.</span>');
                                }
                                
                                return new \Illuminate\Support\HtmlString('Gunakan teks singkat. Karakter: <strong>' . $count . '/100</strong>. Jika teks menyentuh batas 100, akan otomatis menjadi efek berjalan (Marquee) di HP.');
                            })

                            ->required()
                            ->columnSpanFull(),
                        ColorPicker::make('bg_color')
                            ->label('Warna Background')
                            ->default('#000000'),
                        ColorPicker::make('text_color')
                            ->label('Warna Teks')
                            ->default('#ffffff'),
                        Toggle::make('is_active')
                            ->label('Aktifkan Topbar')
                            ->default(true),
                    ])->columns(2),
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
        $announcement = AnnouncementModel::firstOrNew([]);
        $announcement->fill($data);
        $announcement->save();

        Notification::make()
            ->title('Pengumuman topbar berhasil disimpan!')
            ->success()
            ->send();
    }
}
