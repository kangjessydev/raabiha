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

Route::get('/blog/{slug}', function ($slug) {
    return view('blog-detail', ['slug' => $slug]);
});

Route::get('/cart', function () {
    return view('cart');
});

Route::get('/checkout', function () {
    return view('checkout');
});

Route::get('/account', function () {
    return view('account');
});

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

Route::get('/promo', function () {
    return view('promo');
});
