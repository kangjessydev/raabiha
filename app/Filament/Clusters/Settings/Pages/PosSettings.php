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

class PosSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-printer';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Pengaturan POS & Printer';
    protected static ?string $title = 'Pengaturan POS & Printer Thermal';
    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;
    
    protected string $view = 'filament.clusters.settings.pages.pos-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        $canUpdate = auth()->user()->can('Update:PosSettings');
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->disabled(!$canUpdate)
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Struk Thermal')
                            ->icon('heroicon-o-document-text')
                            ->components([
                                Forms\Components\Toggle::make('pos_receipt_logo_enabled')
                                    ->label('Tampilkan Logo Toko di Struk')
                                    ->default(false),
                                Forms\Components\Textarea::make('pos_receipt_header')
                                    ->label('Header Struk')
                                    ->helperText('Teks di bagian atas struk, misalnya alamat dan nomor telepon. (Maksimal 3-4 baris)')
                                    ->rows(3)
                                    ->default("TOKO RAABIHA\nJl. Contoh No. 123\nTelp: 08123456789"),
                                Forms\Components\Textarea::make('pos_receipt_footer')
                                    ->label('Footer Struk')
                                    ->helperText('Teks di bagian bawah struk, misalnya ucapan terima kasih.')
                                    ->rows(2)
                                    ->default("Terima Kasih\nBarang yang sudah dibeli tidak dapat ditukar/dikembalikan"),
                                Forms\Components\Select::make('pos_paper_size')
                                    ->label('Ukuran Kertas')
                                    ->options([
                                        '58' => '58mm (Printer Portabel)',
                                        '80' => '80mm (Printer Desktop)',
                                    ])
                                    ->default('58')
                                    ->required(),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Perangkat (Hardware)')
                            ->icon('heroicon-o-cpu-chip')
                            ->components([
                                Forms\Components\Toggle::make('pos_auto_cut')
                                    ->label('Otomatis Potong Kertas (Auto-Cut)')
                                    ->helperText('Aktifkan jika printer thermal memiliki fitur pisau otomatis.')
                                    ->default(false),
                                Forms\Components\Toggle::make('pos_open_cash_drawer')
                                    ->label('Otomatis Buka Laci Kasir')
                                    ->helperText('Kirim sinyal untuk membuka laci kasir setiap selesai mencetak struk.')
                                    ->default(false),
                                Forms\Components\Select::make('pos_print_copies')
                                    ->label('Jumlah Cetak')
                                    ->options([
                                        '1' => '1 Lembar (Pelanggan)',
                                        '2' => '2 Lembar (Pelanggan + Arsip Toko)',
                                    ])
                                    ->default('1')
                                    ->required(),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Metadata Struk')
                            ->icon('heroicon-o-information-circle')
                            ->components([
                                Forms\Components\Toggle::make('pos_show_cashier_name')
                                    ->label('Tampilkan Nama Kasir')
                                    ->default(true),
                                Forms\Components\Toggle::make('pos_show_date')
                                    ->label('Tampilkan Tanggal & Jam')
                                    ->default(true),
                            ]),
                    ])
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
            ->title('Pengaturan POS berhasil disimpan')
            ->success()
            ->send();
    }
}
