<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Webhook\TripayWebhookController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use App\Http\Middleware\EnsurePosAuthenticated;
use App\Livewire\Account;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\PosLogin;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Blog;
use App\Livewire\BlogDetail;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\ContactPage;
use App\Livewire\OrderDetail;
use App\Livewire\OrderTrack;
use App\Livewire\PosManager;
use App\Livewire\ProductDetail;
use App\Livewire\ResellerDashboard;
use App\Livewire\ResellerRegister;
use App\Livewire\ResellerWelcome;
use App\Livewire\Search;
use App\Livewire\Shop;
use App\Models\Order;
use App\Models\SalesPage;
use App\Models\StaticPage;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Dedicated POS Auth Routes (Subdomain)
Route::domain('pos.'.config('app.domain', 'raabiha.com'))->group(function () {
    Route::get('/login', PosLogin::class)
        ->middleware('guest')
        ->name('pos.login');

    Route::middleware([EnsurePosAuthenticated::class])->group(function () {
        Route::get('/', PosManager::class)->name('pos.index');

        Route::post('/logout', function (Request $request) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('pos.login');
        })->name('pos.logout');
    });
});

Route::get('/', function () {
    return view('home');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', ContactPage::class);

Route::get('/shop', Shop::class);

Route::get('/blog', Blog::class);
Route::get('/search', Search::class);

Route::get('/gallery', function () {
    return view('gallery');
});

Route::get('/product/{slug}', ProductDetail::class);

Route::get('/cart', Cart::class);

Route::get('/blog/{slug}', BlogDetail::class);

Route::middleware(['auth'])->group(function () {
    Route::get('/account', Account::class)->name('account');
    Route::get('/order-detail', OrderDetail::class)->name('order.detail');
    Route::get('/order/{id}/track', OrderTrack::class)->name('order.track');

    Route::post('/logout', function (Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');
});

Route::get('/checkout', Checkout::class)->name('checkout');

Route::get('/order-success', function () {
    $orderNumber = request('order');
    $order = null;
    if ($orderNumber) {
        $order = Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->first();

        if ($order) {
            $isAuthorized = false;

            if (auth()->check()) {
                if ($order->user_id === auth()->id() || auth()->user()->hasAnyRole(['super_admin', 'owner', 'kasir', 'cs', 'marketing'])) {
                    $isAuthorized = true;
                }
            }

            if (session('guest_order_'.$order->order_number) || session('last_placed_order') === $order->order_number) {
                $isAuthorized = true;
            }

            if (request()->hasValidSignature()) {
                $isAuthorized = true;
            }

            if (! $isAuthorized) {
                $order = null;
            }
        }
    }

    return view('order-success', compact('order'));
});

Route::get('/invoice/{orderNumber}', function ($orderNumber) {
    $order = Order::with('items.product')
        ->where('order_number', $orderNumber)
        ->firstOrFail();

    $isAuthorized = false;

    if (auth()->check()) {
        if ($order->user_id === auth()->id() || auth()->user()->hasAnyRole(['super_admin', 'owner', 'kasir', 'cs', 'marketing'])) {
            $isAuthorized = true;
        }
    }

    if (session('guest_order_'.$order->order_number) || session('last_placed_order') === $order->order_number) {
        $isAuthorized = true;
    }

    if (request()->hasValidSignature()) {
        $isAuthorized = true;
    }

    if (! $isAuthorized) {
        abort(403, 'Anda tidak memiliki hak akses untuk melihat invoice pesanan ini.');
    }

    return view('invoice', compact('order'));
})->name('invoice');

Route::get('/login', Login::class)
    ->middleware('guest')
    ->name('login');

Route::get('/register', Register::class)
    ->middleware('guest')
    ->name('register');

Route::get('/forgot-password', ForgotPassword::class)
    ->middleware('guest')
    ->name('password.request');

Route::get('/reset-password/{token}', ResetPassword::class)
    ->middleware('guest')
    ->name('password.reset');

Route::get('/email/verify', VerifyEmail::class)
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ((string) $id !== (string) $user->getKey()) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Anda mengakses tautan verifikasi untuk akun lain. Silakan login dengan akun yang sesuai.');
    }

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Tautan verifikasi tidak valid.');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->route('account')->with('success', 'Email Anda sudah terverifikasi sebelumnya.');
    }

    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    return redirect()->route('account')->with('success', 'Email Anda berhasil diverifikasi!');
})->middleware(['signed'])->name('verification.verify');

Route::get('/reseller-register', ResellerRegister::class);
Route::get('/reseller-welcome', ResellerWelcome::class);
Route::get('/reseller-dashboard', ResellerDashboard::class);

Route::get('/promo', function () {
    return view('promo');
});

// Webhooks
Route::post('/webhook/tripay', [TripayWebhookController::class, 'handle']);
Route::post('/webhook/xendit', [XenditWebhookController::class, 'handle']);

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Dynamic Pages (Catch-all route)
Route::get('/{slug}', function ($slug) {
    // Cek di Halaman Statis
    $staticPage = StaticPage::where('slug', $slug)
        ->where('is_active', true)
        ->first();

    if ($staticPage) {
        return view('static-page', ['page' => $staticPage]);
    }

    // Cek di Sales Page
    $salesPage = SalesPage::where('slug', $slug)
        ->where('is_active', true)
        ->first();

    if ($salesPage) {
        return view('sales-page', ['page' => $salesPage]);
    }

    abort(404);
});
