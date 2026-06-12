<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Inquiry;
use App\Models\Order;
use App\Models\PostComment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Observers\InquiryObserver;
use App\Observers\OrderObserver;
use App\Observers\PostCommentObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductReviewObserver;
use App\Observers\ProductVariantObserver;
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
        // Daftarkan Observer Order untuk auto-record Buku Kas & notifikasi lonceng
        Order::observe(OrderObserver::class);

        // Daftarkan Observer Inquiry untuk notifikasi lonceng pesan masuk
        Inquiry::observe(InquiryObserver::class);

        // Daftarkan Observer untuk komentar blog
        PostComment::observe(PostCommentObserver::class);

        // Daftarkan Observer untuk ulasan/review produk
        ProductReview::observe(ProductReviewObserver::class);

        // Daftarkan Observer untuk stok produk & varian
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);

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

        \Livewire\on('dehydrate', function ($component, $context) {
            if (isset($context->effects['returns'])) {
                foreach ($context->effects['returns'] as $key => $value) {
                    if ($value instanceof \Symfony\Component\HttpFoundation\Response) {
                        unset($context->effects['returns'][$key]);
                    }
                }
                $context->effects['returns'] = array_values($context->effects['returns']);
                if (empty($context->effects['returns'])) {
                    unset($context->effects['returns']);
                }
            }
        });
    }
}
