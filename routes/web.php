<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/shop', \App\Livewire\Shop::class);

Route::get('/blog', function () {
    return view('blog');
});

Route::get('/gallery', function () {
    return view('gallery');
});

Route::get('/product/{slug}', \App\Livewire\ProductDetail::class);

Route::get('/cart', \App\Livewire\Cart::class);

Route::get('/blog/{slug}', function ($slug) {
    $post = \App\Models\Post::where('slug', $slug)->first();
    return view('blog-detail', ['slug' => $slug, 'post' => $post]);
});

Route::get('/checkout', \App\Livewire\Checkout::class);

Route::get('/account', \App\Livewire\Account::class);

Route::get('/order-success', function () {
    return view('order-success');
});

Route::get('/order-detail', function () {
    return view('order-detail');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/reseller-register', \App\Livewire\ResellerRegister::class);
Route::get('/reseller-dashboard', \App\Livewire\ResellerDashboard::class);

Route::get('/promo', function () {
    return view('promo');
});

// Webhooks
Route::post('/webhook/tripay', [\App\Http\Controllers\Webhook\TripayWebhookController::class, 'handle']);
