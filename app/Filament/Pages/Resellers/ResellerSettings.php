<?php

namespace App\Filament\Pages\Resellers;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Models\SiteSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResellerSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = ECommerceCluster::class;
    
    public static function getNavigationIcon(): ?string { return 'heroicon-o-cog-6-tooth'; }
    public static function getNavigationGroup(): ?string { return 'Reseller'; }
    public static function getNavigationLabel(): string { return 'Pengaturan Reseller'; }
    public function getTitle(): string { return 'Pengaturan Reseller'; }

    protected string $view = 'filament.pages.resellers.reseller-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::whereIn('key', [
            'reseller_min_deposit',
            'reseller_discount_percent',
            'reseller_terms'
        ])->pluck('value', 'key')->toArray();

        $this->form->fill([
            'reseller_min_deposit' => $settings['reseller_min_deposit'] ?? 100000,
            'reseller_discount_percent' => $settings['reseller_discount_percent'] ?? 10,
            'reseller_terms' => $settings['reseller_terms'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ketentuan & Diskon Reseller')
                    ->schema([
                        TextInput::make('reseller_min_deposit')
                            ->label('Minimum Deposit Awal (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('reseller_discount_percent')
                            ->label('Persentase Diskon Reseller (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        RichEditor::make('reseller_terms')
                            ->label('Syarat & Ketentuan Reseller')
                            ->columnSpanFull(),
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
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Pengaturan reseller berhasil disimpan!')
            ->success()
            ->send();
    }
}
