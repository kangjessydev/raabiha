<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;
use Awcodes\Curator\Models\Media;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Helper untuk meresolve media URL secara aman
        $resolveMediaUrl = function ($settingKey) {
            try {
                if (Schema::hasTable('site_settings')) {
                    $mediaId = SiteSetting::where('key', $settingKey)->value('value');
                    if ($mediaId) {
                        $media = Media::find($mediaId);
                        if ($media) {
                            return $media->url;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently catch database or missing table errors
            }
            return null;
        };

        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->domain('admin.' . env('APP_DOMAIN', 'raabiha.com'))
            ->font('Inter')
            ->darkMode(false)
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Profil Saya')
                    ->url(fn (): string => \App\Filament\Pages\MyProfile::getUrl())
                    ->icon('heroicon-m-user-circle'),
                \Filament\Navigation\MenuItem::make()
                    ->label('Terminal POS Kasir')
                    ->url(fn(): string => route('pos.index'))
                    ->icon('heroicon-m-computer-desktop')
                    ->openUrlInNewTab(),
                \Filament\Navigation\MenuItem::make()
                    ->label('Kunjungi Website')
                    ->url(fn(): string => config('app.url'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->openUrlInNewTab(),
                \Filament\Navigation\MenuItem::make()
                    ->label('Lihat Katalog')
                    ->url(fn(): string => config('app.url') . '/shop')
                    ->icon('heroicon-m-shopping-bag')
                    ->openUrlInNewTab(),
            ])

            ->colors([
                'primary' => \Filament\Support\Colors\Color::Emerald,
                'gray' => \Filament\Support\Colors\Color::Stone,
            ])
            ->brandName(
                fn() =>
                (Schema::hasTable('site_settings')
                    ? SiteSetting::where('key', 'site_name')->value('value')
                    : null) ?: 'Raabiha Dashboard'
            )
            ->homeUrl('/')
            ->brandLogo(fn() => $resolveMediaUrl('site_logo_light'))
            ->darkModeBrandLogo(fn() => $resolveMediaUrl('site_logo_dark'))
            ->brandLogoHeight('2.5rem')
            ->favicon(fn() => $resolveMediaUrl('site_favicon'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->navigationGroups([
                'Penjualan & Transaksi',
                'Katalog & Stok Barang',
                'Pelanggan & Reseller',
                'Kasir & Toko Fisik (POS)',
                'Pemasaran',
                'Manajemen Konten',
                'Laporan & Keuangan',
                'Pengaturan Toko & Sistem',
                'Pengguna & Hak Akses',
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                \Awcodes\Curator\CuratorPlugin::make()
                    ->label('Galeri Media')
                    ->pluralLabel('Galeri Media')
                    ->navigationIcon('heroicon-o-photo')
                    ->navigationGroup(null)
                    ->navigationSort(2)
                    ->registerNavigation(true),
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\RedirectKasirToPos::class,
            ]);
    }
}
