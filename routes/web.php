<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', \App\Livewire\ContactPage::class);

Route::get('/shop', \App\Livewire\Shop::class);

Route::get('/blog', \App\Livewire\Blog::class);
Route::get('/search', \App\Livewire\Search::class);

Route::get('/gallery', function () {
    return view('gallery');
});

Route::get('/product/{slug}', \App\Livewire\ProductDetail::class);

Route::get('/cart', \App\Livewire\Cart::class);

Route::get('/blog/{slug}', \App\Livewire\BlogDetail::class);

Route::middleware(['auth'])->group(function () {
    Route::get('/account', \App\Livewire\Account::class)->name('account');
    Route::get('/order-detail', \App\Livewire\OrderDetail::class)->name('order.detail');

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');

Route::get('/order-success', function () {
    $orderNumber = request('order');
    $order = null;
    if ($orderNumber) {
        $order = \App\Models\Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->first();
    }
    return view('order-success', compact('order'));
});

Route::get('/invoice/{orderNumber}', function ($orderNumber) {
    $order = \App\Models\Order::with('items.product')
        ->where('order_number', $orderNumber)
        ->firstOrFail();
    
    return view('invoice', compact('order'));
})->name('invoice');

Route::get('/login', \App\Livewire\Auth\Login::class)
    ->middleware('guest')
    ->name('login');

Route::get('/register', \App\Livewire\Auth\Register::class)
    ->middleware('guest')
    ->name('register');

Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)
    ->middleware('guest')
    ->name('password.request');

Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)
    ->middleware('guest')
    ->name('password.reset');

Route::get('/email/verify', \App\Livewire\Auth\VerifyEmail::class)
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('account')->with('success', 'Email Anda berhasil diverifikasi!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/reseller-register', \App\Livewire\ResellerRegister::class);
Route::get('/reseller-welcome', \App\Livewire\ResellerWelcome::class);
Route::get('/reseller-dashboard', \App\Livewire\ResellerDashboard::class);

Route::get('/promo', function () {
    return view('promo');
});

// Webhooks
Route::post('/webhook/tripay', [\App\Http\Controllers\Webhook\TripayWebhookController::class, 'handle']);
Route::post('/webhook/xendit', [\App\Http\Controllers\Webhook\XenditWebhookController::class, 'handle']);

// Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

// Dynamic Pages (Catch-all route)
Route::get('/{slug}', function ($slug) {
    // Cek di Halaman Statis
    $staticPage = \App\Models\StaticPage::where('slug', $slug)
        ->where('is_active', true)
        ->first();

    if ($staticPage) {
        return view('static-page', ['page' => $staticPage]);
    }

    // Cek di Sales Page
    $salesPage = \App\Models\SalesPage::where('slug', $slug)
        ->where('is_active', true)
        ->first();

    if ($salesPage) {
        return view('sales-page', ['page' => $salesPage]);
    }

    abort(404);
});
