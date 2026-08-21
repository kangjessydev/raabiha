<?php

namespace App\Filament\Pages\Settings;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Schemas\Components\Fieldset;
use Filament\Notifications\Notification;

class PosHardwareSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-printer';
    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';
    protected static ?string $navigationLabel = 'Perangkat & Struk POS';
    protected static ?string $title = 'Pengaturan Perangkat & Struk POS';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.settings.pos-settings-form';

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
                \Filament\Schemas\Components\Section::make('Printer & Struk Thermal')
                    ->description('Konfigurasi tata letak dan pencetakan struk transaksi kasir.')
                    ->icon('heroicon-o-document-text')
                    ->components([
                        Fieldset::make('Logo Toko & Layar POS')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Toggle::make('pos_receipt_logo_enabled')
                                    ->label('Tampilkan Logo Toko di Struk Thermal')
                                    ->helperText('Jika diaktifkan, logo utama di bawah ini akan otomatis dicetak di bagian paling atas struk thermal.')
                                    ->default(false),
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('pos_ui_logo')
                                    ->label('Logo Utama Toko / POS')
                                    ->helperText('Logo toko yang muncul di sudut kiri atas layar POS dan bagian atas struk thermal.')
                                    ->buttonLabel('Pilih / Unggah Logo')
                                    ->extraAttributes([
                                        'class' => '[&_.curator-picker-preview]:!rounded-full [&_.curator-picker-preview]:!w-24 [&_.curator-picker-preview]:!h-24 [&_.curator-picker-preview_img]:!rounded-full [&_.curator-picker-preview_img]:!object-cover [&_.curator-picker-button]:!rounded-lg',
                                    ]),
                            ]),
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
                        Forms\Components\Toggle::make('pos_show_cashier_name')
                            ->label('Tampilkan Nama Kasir di Header POS')
                            ->default(true),
                        Forms\Components\Toggle::make('pos_show_date')
                            ->label('Tampilkan Tanggal & Jam di Header POS')
                            ->default(true),

                    ]),
                \Filament\Schemas\Components\Section::make('Perangkat Kasir (Hardware)')
                    ->description('Pengaturan otomatisasi pisau pemotong dan laci kasir (Cash Drawer).')
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
                        Forms\Components\Select::make('pos_paper_size')
                            ->label('Ukuran Kertas Printer Thermal')
                            ->options([
                                '58' => '58mm (Printer Portabel)',
                                '80' => '80mm (Printer Desktop)',
                            ])
                            ->default('58')
                            ->required(),
                        Forms\Components\Select::make('pos_print_copies')
                            ->label('Jumlah Cetak Struk per Transaksi')
                            ->options([
                                '1' => '1 Lembar (Pelanggan)',
                                '2' => '2 Lembar (Pelanggan + Arsip Toko)',
                            ])
                            ->default('1')
                            ->required(),
                    ]),

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
            ->title('Pengaturan Perangkat & Struk berhasil disimpan')
            ->success()
            ->send();
    }
}
