<?php

namespace App\Filament\Pages\Resellers;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

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
    use HasPageShield;

    use InteractsWithForms;

    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 42;

    
    public static function getNavigationIcon(): ?string { return 'heroicon-o-cog-6-tooth'; }
    public static function getNavigationGroup(): string|\BackedEnum|null { return \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Reseller; }
    public static function getNavigationLabel(): string { return 'Pengaturan Reseller'; }
    public function getTitle(): string { return 'Pengaturan Reseller'; }

    protected string $view = 'filament.pages.resellers.reseller-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::whereIn('key', [
            'reseller_registration_open',
            'reseller_min_deposit',
            'reseller_discount_percent',
            'reseller_terms',
            'reseller_banks',
            'reseller_whatsapp_payment'
        ])->pluck('value', 'key')->toArray();

        $this->form->fill([
            'reseller_registration_open' => isset($settings['reseller_registration_open']) ? $settings['reseller_registration_open'] == '1' : false,
            'reseller_min_deposit' => $settings['reseller_min_deposit'] ?? 100000,
            'reseller_discount_percent' => $settings['reseller_discount_percent'] ?? 10,
            'reseller_terms' => $settings['reseller_terms'] ?? '',
            'reseller_banks' => isset($settings['reseller_banks']) ? json_decode($settings['reseller_banks'], true) : [],
            'reseller_whatsapp_payment' => $settings['reseller_whatsapp_payment'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $canUpdate = auth()->user()->can('Update:User');
        return $schema
            ->components([
                Section::make('Ketentuan & Diskon Reseller')
                    ->disabled(!$canUpdate)
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('reseller_registration_open')
                            ->label('Buka Pendaftaran Reseller')
                            ->helperText('Jika dinonaktifkan, menu pendaftaran reseller akan disembunyikan dari halaman akun pelanggan umum. (Pelanggan yang sudah menjadi reseller tetap bisa mengakses dasbor)')
                            ->default(false)
                            ->columnSpanFull(),
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
                
                Section::make('Informasi Pembayaran (Bank)')
                    ->disabled(!$canUpdate)
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('reseller_banks')
                            ->label('Daftar Rekening Bank')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank (contoh: BCA, Mandiri)')
                                    ->required(),
                                TextInput::make('account_number')
                                    ->label('Nomor Rekening')
                                    ->required(),
                                TextInput::make('account_name')
                                    ->label('Atas Nama')
                                    ->required(),
                             ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->defaultItems(1),
                        TextInput::make('reseller_whatsapp_payment')
                            ->label('Nomor WhatsApp Admin (Untuk Konfirmasi)')
                            ->placeholder('Contoh: 6281234567890')
                            ->helperText('Nomor ini akan digunakan sebagai link untuk tombol Kirim Bukti Pembayaran.')
                            ->required()
                            ->columnSpanFull(),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('Update:User'), 403);
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            $valueToSave = is_array($value) ? json_encode($value) : $value;
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $valueToSave]
            );
        }

        Notification::make()
            ->title('Pengaturan reseller berhasil disimpan!')
            ->success()
            ->send();
    }
}
