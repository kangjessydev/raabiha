<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Order;
use App\Observers\OrderObserver;
use Awcodes\Curator\Facades\Curator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan Observer Order untuk auto-record Buku Kas
        Order::observe(OrderObserver::class);

        Curator::acceptedFileTypes([
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'video/mp4',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
        ])->maxSize(10240); // 10MB limit

        // Implicitly grant "Super Admin" role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::BODY_END,
            fn (): string => \Illuminate\Support\Facades\Blade::render('
                @auth
                <script>
                    document.addEventListener("livewire:initialized", () => {
                        let lastCount = {{ auth()->user()->unreadNotifications()->count() }};
                        Livewire.hook("commit", ({ component, commit, respond, succeed, fail }) => {
                            if (component.name === "database-notifications" || component.name === "filament-notifications.database-notifications") {
                                succeed(({ snapshot, effect }) => {
                                    setTimeout(() => {
                                        let badges = document.querySelectorAll(".fi-modal-btn-badge, .fi-icon-btn-badge, .fi-badge");
                                        let currentCount = 0;
                                        badges.forEach(b => {
                                            let text = b.innerText.trim();
                                            if (text) {
                                                let count = parseInt(text);
                                                if (count > currentCount) currentCount = count;
                                            }
                                        });
                                        
                                        if (currentCount > lastCount) {
                                            let audio = new Audio("{{ asset(\'assets/audio/notification.mp3\') }}");
                                            audio.play().catch(e => console.log("Audio play prevented", e));
                                        }
                                        lastCount = currentCount;
                                    }, 100);
                                });
                            }
                        });
                    });
                </script>
                @endauth
            ')
        );
    }
}
