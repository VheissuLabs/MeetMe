<?php

use App\Http\Controllers\Auth\SocialCallbackController;
use App\Http\Controllers\Auth\SocialRedirectController;
use App\Http\Controllers\MeetingAnswerController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingResolveController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', SocialRedirectController::class)->name('social.redirect');
    Route::get('auth/{provider}/callback', SocialCallbackController::class)->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::post('meetings', [MeetingController::class, 'store'])
        ->middleware('throttle:meetme-scan')
        ->name('meetings.store');
    Route::get('meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::patch('meetings/{meeting}/answer', MeetingAnswerController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('meetings.answer');
    Route::patch('meetings/{meeting}/resolve', MeetingResolveController::class)->name('meetings.resolve');
});

require __DIR__.'/settings.php';
