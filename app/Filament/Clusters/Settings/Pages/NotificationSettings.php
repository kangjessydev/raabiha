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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Mail;

class NotificationSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $title = 'Pengaturan Notifikasi & SMTP';
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
        $canUpdate = auth()->user()->can('Update:GlobalSettings');
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('NotificationSettingsTabs')
                    ->disabled(!$canUpdate)
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Konfigurasi Email / SMTP')
                            ->components([
                                Forms\Components\Radio::make('mail_driver')
                                    ->label('Driver Pengiriman Email')
                                    ->options([
                                        'log' => 'Log Driver (Testing)',
                                        'gmail' => 'Gmail SMTP',
                                        'brevo' => 'Brevo SMTP (Rekomendasi)',
                                        'custom' => 'Custom SMTP (Lainnya)',
                                    ])
                                    ->default('log')
                                    ->inline()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('driver_info')
                                    ->label('')
                                    ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                        $driver = $get('mail_driver');
                                        if ($driver === 'gmail') {
                                            return new HtmlString('
                                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 text-sm dark:bg-blue-950/20 dark:border-blue-900/50">
                                                    <strong class="text-blue-800 dark:text-blue-400">💡 Informasi Driver - Gmail SMTP:</strong>
                                                    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300">
                                                        <li><span class="font-semibold text-green-600">Kelebihan:</span> Sangat mudah disetup, gratis 500 email/hari, reputasi IP Google tinggi (pasti masuk inbox).</li>
                                                        <li><span class="font-semibold text-red-600">Kekurangan:</span> Batas harian rendah. Jika Anda mengirim email notifikasi admin ke alamat Gmail yang sama, Gmail akan menyembunyikannya dari Kotak Masuk (hanya masuk folder Sent/Terkirim).</li>
                                                        <li><span class="font-semibold">Penting:</span> Pastikan <strong>Email Penerima Notifikasi Admin</strong> menggunakan email lain agar notifikasi pesanan masuk tetap muncul di Inbox.</li>
                                                    </ul>
                                                </div>
                                            ');
                                        }
                                        if ($driver === 'brevo') {
                                            return new HtmlString('
                                                <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200 text-sm dark:bg-emerald-950/20 dark:border-emerald-900/50">
                                                    <strong class="text-emerald-800 dark:text-emerald-400">💡 Informasi Driver - Brevo SMTP (Sangat Direkomendasikan):</strong>
                                                    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300">
                                                        <li><span class="font-semibold text-green-600">Kelebihan:</span> Didesain untuk e-commerce, kuota gratis 300 email/hari (9.000/bulan), analitik pengiriman lengkap di web Brevo, mendukung pengiriman ke diri sendiri tanpa masalah.</li>
                                                        <li><span class="font-semibold text-red-600">Kekurangan:</span> Memerlukan verifikasi nama domain (DNS: SPF & DKIM) di awal pembuatan akun Brevo agar terjamin tidak masuk spam.</li>
                                                    </ul>
                                                </div>
                                            ');
                                        }
                                        if ($driver === 'custom') {
                                            return new HtmlString('
                                                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 text-sm dark:bg-amber-950/20 dark:border-amber-900/50">
                                                    <strong class="text-amber-800 dark:text-amber-400">💡 Informasi Driver - Custom SMTP:</strong>
                                                    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300">
                                                        <li>Gunakan opsi ini jika Anda memiliki server mail hosting mandiri atau menggunakan penyedia layanan email transaksional lain seperti Mailgun, Postmark, AWS SES, cPanel Mail, dsb.</li>
                                                    </ul>
                                                </div>
                                            ');
                                        }
                                        return new HtmlString('
                                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm dark:bg-gray-900/20 dark:border-gray-800/50">
                                                <strong class="text-gray-800 dark:text-gray-400">💡 Informasi Driver - Log Driver:</strong>
                                                <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300">
                                                    <li>Email tidak dikirim ke internet, melainkan hanya ditulis ke berkas log server di <code>storage/logs/laravel.log</code>. Sangat berguna untuk pengujian di komputer lokal.</li>
                                                </ul>
                                            </div>
                                        ');
                                    })
                                    ->columnSpanFull(),

                                // Kredensial Gmail
                                \Filament\Schemas\Components\Group::make([
                                    Forms\Components\TextInput::make('gmail_username')
                                        ->label('Username / Email Gmail')
                                        ->email()
                                        ->placeholder('contoh@gmail.com')
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('gmail_password')
                                        ->label('Google App Password')
                                        ->password()
                                        ->helperText('Bukan password Gmail biasa. Anda harus membuat "App Password" 16 digit di akun Google Anda dengan mengaktifkan verifikasi 2 langkah.')
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('mail_driver') === 'gmail')
                                    ->columnSpanFull(),

                                // Kredensial Brevo
                                \Filament\Schemas\Components\Group::make([
                                    Forms\Components\TextInput::make('brevo_username')
                                        ->label('SMTP Username / Email Login Brevo')
                                        ->placeholder('username@domain.com')
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('brevo_password')
                                        ->label('Brevo SMTP Key')
                                        ->password()
                                        ->placeholder('xkeysib-...')
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('mail_driver') === 'brevo')
                                    ->columnSpanFull(),

                                // Kredensial Custom SMTP
                                \Filament\Schemas\Components\Group::make([
                                    Forms\Components\TextInput::make('custom_host')
                                        ->label('SMTP Host')
                                        ->placeholder('mail.domain.com')
                                        ->required(),
                                    Forms\Components\TextInput::make('custom_port')
                                        ->label('SMTP Port')
                                        ->numeric()
                                        ->default(587)
                                        ->required(),
                                    Forms\Components\Select::make('custom_encryption')
                                        ->label('SMTP Encryption')
                                        ->options([
                                            'none' => 'None',
                                            'ssl' => 'SSL',
                                            'tls' => 'TLS',
                                        ])
                                        ->default('tls')
                                        ->required(),
                                    Forms\Components\TextInput::make('custom_username')
                                        ->label('SMTP Username')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('custom_password')
                                        ->label('SMTP Password')
                                        ->password()
                                        ->nullable(),
                                ])
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('mail_driver') === 'custom')
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Identitas Pengirim & Penerima')
                            ->components([
                                Forms\Components\TextInput::make('mail_from_address')
                                    ->label('Email Pengirim (Sender Address)')
                                    ->email()
                                    ->default('noreply@raabiha.com')
                                    ->required(),
                                Forms\Components\TextInput::make('mail_from_name')
                                    ->label('Nama Pengirim (Sender Name)')
                                    ->default('Raabiha Store')
                                    ->required(),
                                Forms\Components\TextInput::make('mail_admin_recipient')
                                    ->label('Email Penerima Notifikasi Admin')
                                    ->email()
                                    ->helperText('Notifikasi pesanan baru, refund, dan aktivitas admin lainnya akan dikirim ke alamat email ini.')
                                    ->required(),
                            ])->columns(3),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        $canUpdate = auth()->user()->can('Update:GlobalSettings');
        return [
            \Filament\Actions\Action::make('test_email')
                ->label('Kirim Email Uji Coba')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->disabled(!$canUpdate)
                ->form([
                    Forms\Components\TextInput::make('test_recipient')
                        ->label('Email Penerima Uji Coba')
                        ->email()
                        ->required()
                        ->default(fn() => $this->data['mail_admin_recipient'] ?? ''),
                ])
                ->action(function (array $data) {
                    $this->sendTestEmail($data['test_recipient']);
                }),
        ];
    }

    private function sendTestEmail(string $recipient): void
    {
        $config = $this->data;
        $driver = $config['mail_driver'] ?? 'log';
        
        $host = null;
        $port = null;
        $encryption = null;
        $username = null;
        $password = null;

        if ($driver === 'gmail') {
            $host = 'smtp.gmail.com';
            $port = 587;
            $encryption = 'tls';
            $username = $config['gmail_username'] ?? '';
            $password = $config['gmail_password'] ?? '';
        } elseif ($driver === 'brevo') {
            $host = 'smtp-relay.brevo.com';
            $port = 587;
            $encryption = 'tls';
            $username = $config['brevo_username'] ?? '';
            $password = $config['brevo_password'] ?? '';
        } elseif ($driver === 'custom') {
            $host = $config['custom_host'] ?? '';
            $port = $config['custom_port'] ?? 587;
            $encryption = $config['custom_encryption'] ?? 'tls';
            $username = $config['custom_username'] ?? '';
            $password = $config['custom_password'] ?? '';
        }

        // Terapkan konfigurasi sementara untuk uji coba
        config([
            'mail.default' => $driver === 'log' ? 'log' : 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.from.address' => $config['mail_from_address'] ?? 'noreply@raabiha.com',
            'mail.from.name' => $config['mail_from_name'] ?? 'Raabiha Store',
        ]);

        // Reset mail manager agar membuat koneksi baru dengan config di atas
        app()->forgetInstance('mail.manager');
        Mail::clearResolvedInstances();

        try {
            Mail::raw('Halo! Ini adalah email uji coba dari sistem pengaturan notifikasi Raabiha E-Commerce. Jika Anda menerima email ini, konfigurasi SMTP Anda sudah bekerja dengan benar.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('🔔 Uji Coba Koneksi SMTP Raabiha');
            });

            Notification::make()
                ->title('Email uji coba berhasil dikirim!')
                ->body('Silakan periksa kotak masuk email: ' . $recipient)
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengirim email uji coba')
                ->body('Terjadi kesalahan koneksi SMTP: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function submit(): void
    {
        abort_unless(auth()->user()->can('Update:GlobalSettings'), 403);
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan notifikasi berhasil disimpan')
            ->success()
            ->send();
    }
}
