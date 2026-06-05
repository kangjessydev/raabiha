<?php

namespace App\Filament\Clusters\Marketing\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use App\Filament\Clusters\Marketing\MarketingCluster;

class GallerySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = MarketingCluster::class;
    protected static string | \UnitEnum | null $navigationGroup = 'CMS';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';
    protected static ?int $navigationSort = 3;
    public static function getNavigationLabel(): string { return 'Galeri Builder'; }
    public function getTitle(): string { return 'Galeri Builder'; }

    protected string $view = 'filament.pages.gallery-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::where('key', 'gallery_content')->value('value');
        $this->form->fill([
            'gallery_content' => $settings ? json_decode($settings, true) : [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
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
                                \Filament\Forms\Components\Repeater::make('features')
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
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Galeri')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        SiteSetting::updateOrCreate(
            ['key' => 'gallery_content'],
            ['value' => json_encode($data['gallery_content'])]
        );

        Notification::make()
            ->title('Halaman Galeri berhasil diperbarui!')
            ->success()
            ->send();
    }
}
