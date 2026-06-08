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
    return view('order-success');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/reseller-register', \App\Livewire\ResellerRegister::class);
Route::get('/reseller-welcome', \App\Livewire\ResellerWelcome::class);
Route::get('/reseller-dashboard', \App\Livewire\ResellerDashboard::class);

Route::get('/promo', function () {
    return view('promo');
});

// Webhooks
Route::post('/webhook/tripay', [\App\Http\Controllers\Webhook\TripayWebhookController::class, 'handle']);

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
