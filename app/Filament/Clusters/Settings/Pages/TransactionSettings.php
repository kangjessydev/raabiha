<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;

class TransactionSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Transaksi & Refund';
    protected static ?string $title = 'Pengaturan Transaksi & Refund';
    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;
    
    protected string $view = 'filament.clusters.settings.pages.global-settings';

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
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Refund & Pengembalian')
                            ->components([
                                Forms\Components\RichEditor::make('refund_terms')
                                    ->label('Syarat & Ketentuan Refund')
                                    ->helperText('Penjelasan detail mengenai syarat, cara, dan ketentuan pengembalian dana.'),
                                Forms\Components\TextInput::make('refund_claim_days')
                                    ->label('Batas Waktu Klaim (Hari)')
                                    ->numeric()
                                    ->default(7)
                                    ->helperText('Maksimal hari setelah pesanan Selesai/Terkirim bagi pelanggan untuk dapat mengajukan refund.'),
                                Forms\Components\TextInput::make('refund_admin_phone')
                                    ->label('Nomor WA Admin Refund')
                                    ->helperText('Nomor WhatsApp admin khusus bagian Refund (gunakan format 628...).')
                                    ->prefix('+62'),
                                Forms\Components\Textarea::make('refund_template_approved')
                                    ->label('Template Pesan WA - Disetujui')
                                    ->default('Halo {name}, pengajuan refund untuk pesanan #{order} senilai Rp{amount} telah DISETUJUI. Tim Finance kami akan segera memproses transfer ke rekening Anda.')
                                    ->helperText('Gunakan parameter ini: {name} (Nama pelanggan), {order} (No. Pesanan), {amount} (Nominal refund)')
                                    ->rows(3),
                                Forms\Components\Textarea::make('refund_template_rejected')
                                    ->label('Template Pesan WA - Ditolak')
                                    ->default('Halo {name}, mohon maaf pengajuan refund untuk pesanan #{order} senilai Rp{amount} DITOLAK. Catatan: {notes}')
                                    ->helperText('Tersedia parameter tambahan: {notes} (Catatan/alasan penolakan dari admin)')
                                    ->rows(3),
                                Forms\Components\Textarea::make('refund_template_completed')
                                    ->label('Template Pesan WA - Selesai')
                                    ->default('Halo {name}, dana refund untuk pesanan #{order} senilai Rp{amount} telah SELESAI DITRANSFER ke rekening {bank} Anda. Silakan cek mutasi rekening Anda.')
                                    ->helperText('Tersedia parameter tambahan: {bank} (Nama bank rekening tujuan pelanggan)')
                                    ->rows(3),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Mode Libur')
                            ->components([
                                Forms\Components\Toggle::make('store_holiday_mode')
                                    ->label('Aktifkan Mode Libur (Tutup Toko)')
                                    ->helperText('Jika diaktifkan, sebuah spanduk peringatan akan muncul di atas halaman website.')
                                    ->default(false),
                                Forms\Components\Textarea::make('store_holiday_message')
                                    ->label('Pesan Pengumuman Libur')
                                    ->helperText('Contoh: "Toko sedang libur Lebaran."')
                                    ->default('Mohon maaf, toko kami sedang libur. Semua pesanan yang masuk akan diproses dan dikirim setelah kami kembali beroperasi.')
                                    ->rows(3),
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
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan Transaksi berhasil disimpan')
            ->success()
            ->send();
    }
}
