<?php

use App\Http\Controllers\Auth\SocialCallbackController;
use App\Http\Controllers\Auth\SocialRedirectController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', SocialRedirectController::class)->name('social.redirect');
    Route::get('auth/{provider}/callback', SocialCallbackController::class)->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
