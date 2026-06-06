<?php

namespace App\Filament\Pages;

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
                            ->helperText('Gunakan teks singkat (Maks disarankan 100 karakter). Jika teks terlalu panjang, akan otomatis menjadi efek berjalan (Marquee) di HP.')
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
