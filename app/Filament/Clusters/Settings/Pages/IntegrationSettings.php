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

class IntegrationSettings extends Page implements HasForms
{
    use HasPageShield;

    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Integrasi & API';
    protected static ?string $title = 'Pengaturan Integrasi & API';
    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;
    
    protected string $view = 'filament.clusters.settings.pages.global-settings';

    public ?array $data = [];
    public bool $unlockedTripay = false;
    public bool $unlockedXendit = false;
    public bool $unlockedRajaOngkir = false;
    public bool $unlockedBinderByte = false;

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Payment Gateway')
                            ->visible(fn () => auth()->user()->hasRole('super_admin'))
                            ->components([
                                Forms\Components\Radio::make('active_payment_gateway')
                                    ->label('Payment Gateway Aktif')
                                    ->options([
                                        'tripay' => 'Tripay',
                                        'xendit' => 'Xendit',
                                    ])
                                    ->default('tripay')
                                    ->inline()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),
                                \Filament\Schemas\Components\Group::make([
                                    Forms\Components\Select::make('tripay_mode')
                                        ->label('Tripay Environment Mode')
                                        ->options([
                                            'sandbox' => 'Sandbox (Testing)',
                                            'production' => 'Production (Live)'
                                        ])
                                        ->default('sandbox')
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('tripay_merchant_code')
                                        ->label('Tripay Merchant Code')
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('tripay_api_key')
                                        ->label('Tripay API Key')
                                        ->password()
                                        ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                        ->suffixAction(
                                            \Filament\Actions\Action::make('unlock_tripay')
                                                ->icon('heroicon-m-lock-closed')
                                                ->color('danger')
                                                ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                                ->requiresConfirmation()
                                                ->form([
                                                    Forms\Components\TextInput::make('password')
                                                        ->label('Password Anda')
                                                        ->password()
                                                        ->required()
                                                        ->currentPassword()
                                                ])
                                                ->action(function (\Livewire\Component $livewire) {
                                                    $livewire->unlockedTripay = true;
                                                })
                                        )
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('tripay_private_key')
                                        ->label('Tripay Private Key')
                                        ->password()
                                        ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedTripay)
                                        ->columnSpanFull(),
                                ])->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_payment_gateway') === 'tripay'),
                                
                                \Filament\Schemas\Components\Group::make([
                                    Forms\Components\TextInput::make('xendit_secret_key')
                                        ->label('Xendit Secret Key')
                                        ->password()
                                        ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedXendit)
                                        ->suffixAction(
                                            \Filament\Actions\Action::make('unlock_xendit')
                                                ->icon('heroicon-m-lock-closed')
                                                ->color('danger')
                                                ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedXendit)
                                                ->requiresConfirmation()
                                                ->form([
                                                    Forms\Components\TextInput::make('password')
                                                        ->password()
                                                        ->required()
                                                        ->currentPassword()
                                                ])
                                                ->action(function (\Livewire\Component $livewire) {
                                                    $livewire->unlockedXendit = true;
                                                })
                                        )
                                        ->columnSpanFull(),
                                ])->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_payment_gateway') === 'xendit'),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Ekspedisi')
                            ->visible(fn () => auth()->user()->hasRole('super_admin'))
                            ->components([
                                Forms\Components\Radio::make('active_shipping_provider')
                                    ->label('Provider Ongkir Aktif')
                                    ->options([
                                        'komerce' => 'Komerce (RajaOngkir)',
                                        'binderbyte' => 'BinderByte Logistics',
                                    ])
                                    ->default('komerce')
                                    ->inline()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('rajaongkir_api_key')
                                    ->label('API Key Ekspedisi (Komerce / RajaOngkir)')
                                    ->password()
                                    ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedRajaOngkir)
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('unlock_rajaongkir')
                                            ->icon('heroicon-m-lock-closed')
                                            ->color('danger')
                                            ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedRajaOngkir)
                                            ->requiresConfirmation()
                                            ->form([
                                                Forms\Components\TextInput::make('password')
                                                    ->password()
                                                    ->required()
                                                    ->currentPassword()
                                            ])
                                            ->action(function (\Livewire\Component $livewire) {
                                                $livewire->unlockedRajaOngkir = true;
                                            })
                                    )
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_shipping_provider') !== 'binderbyte')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('binderbyte_api_key')
                                    ->label('API Key BinderByte')
                                    ->password()
                                    ->readOnly(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedBinderByte)
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('unlock_binderbyte')
                                            ->icon('heroicon-m-lock-closed')
                                            ->color('danger')
                                            ->visible(fn (\Livewire\Component $livewire, ?string $state) => !empty($state) && !$livewire->unlockedBinderByte)
                                            ->requiresConfirmation()
                                            ->form([
                                                Forms\Components\TextInput::make('password')
                                                    ->password()
                                                    ->required()
                                                    ->currentPassword()
                                            ])
                                            ->action(function (\Livewire\Component $livewire) {
                                                $livewire->unlockedBinderByte = true;
                                            })
                                    )
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_shipping_provider') === 'binderbyte')
                                    ->columnSpanFull(),
                                \Filament\Schemas\Components\Actions::make([
                                    \Filament\Actions\Action::make('test_binderbyte_conn')
                                        ->label('Test Koneksi BinderByte')
                                        ->icon('heroicon-m-bolt')
                                        ->color('warning')
                                        ->action(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                            $apiKey = $get('binderbyte_api_key');
                                            if (empty($apiKey)) {
                                                Notification::make()
                                                    ->title('Gagal')
                                                    ->body('Harap masukkan API Key BinderByte terlebih dahulu.')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }
                                            
                                            $response = \Illuminate\Support\Facades\Http::get('https://api.binderbyte.com/wilayah/provinsi', [
                                                'api_key' => $apiKey
                                            ]);
                                            
                                            if ($response->successful() && $response->json('code') == '200') {
                                                Notification::make()
                                                    ->title('Koneksi Sukses')
                                                    ->body('API Key BinderByte valid dan berhasil terhubung.')
                                                    ->success()
                                                    ->send();
                                            } else {
                                                $msg = $response->json('message') ?? 'Respons tidak dikenal dari server.';
                                                Notification::make()
                                                    ->title('Koneksi Gagal')
                                                    ->body('Gagal menghubungkan ke BinderByte: ' . $msg)
                                                    ->danger()
                                                    ->send();
                                            }
                                        })
                                ])
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('active_shipping_provider') === 'binderbyte'),
                            ]),

                            
                        \Filament\Schemas\Components\Tabs\Tab::make('Tracking & Custom Scripts')
                            ->components([
                                Forms\Components\TextInput::make('looker_studio_embed_url')->label('URL Embed Looker Studio')->columnSpanFull(),
                                Forms\Components\TextInput::make('google_analytics_id')->label('ID Google Analytics (GA4)'),
                                Forms\Components\TextInput::make('meta_pixel_id')->label('ID Meta Pixel'),
                                Forms\Components\TextInput::make('tiktok_pixel_id')->label('ID TikTok Pixel'),
                                Forms\Components\Textarea::make('scripts_header')->label('Custom Scripts (Header)')->rows(4)->columnSpanFull(),
                                Forms\Components\Textarea::make('scripts_footer')->label('Custom Scripts (Footer)')->rows(4)->columnSpanFull(),
                            ])->columns(3),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan Integrasi berhasil disimpan')
            ->success()
            ->send();
    }
}
