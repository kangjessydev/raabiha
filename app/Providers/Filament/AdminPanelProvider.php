<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
            ->path('admin')
            ->font('Poppins')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Profil')
                    ->url(fn (): string => \App\Filament\Pages\MyProfile::getUrl())
                    ->icon('heroicon-m-user-circle'),
                \Filament\Navigation\MenuItem::make()
                    ->label('Kunjungi Toko')
                    ->url('/')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->openUrlInNewTab(),
                \Filament\Navigation\MenuItem::make()
                    ->label('Kunjungi Shop')
                    ->url('/shop')
                    ->icon('heroicon-m-shopping-bag')
                    ->openUrlInNewTab(),
            ])
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Emerald,
                'gray' => \Filament\Support\Colors\Color::Stone,
            ])
            ->brandName(fn () => 
                (Schema::hasTable('site_settings') 
                    ? SiteSetting::where('key', 'site_name')->value('value') 
                    : null) ?: 'Raabiha Admin'
            )
            ->homeUrl('/')
            ->brandLogo(fn () => $resolveMediaUrl('site_logo_light'))
            ->darkModeBrandLogo(fn () => $resolveMediaUrl('site_logo_dark'))
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => $resolveMediaUrl('site_favicon'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->topNavigation()
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->navigationGroups([
                'Transaksi',
                'Katalog',
                'Promosi',
                'Reseller',
                'Pengaturan Toko',
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
                    ->label('Media')
                    ->pluralLabel('Media')
                    ->navigationIcon('heroicon-o-photo')
                    ->navigationGroup('Content')
                    ->navigationSort(3)
                    ->registerNavigation(true),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
