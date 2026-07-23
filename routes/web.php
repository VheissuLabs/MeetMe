<?php

use App\Http\Controllers\Auth\SocialCallbackController;
use App\Http\Controllers\Auth\SocialRedirectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MeetController;
use App\Http\Controllers\MeetingAnswerController;
use App\Http\Controllers\MeetingAnswerRedactionController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingResolveController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Public, no auth — designed for the venue TV.
Route::get('leaderboard', LeaderboardController::class)->name('leaderboard');

// Deep-link from a scanned QR. Guests are bounced to auth (intended URL
// stashed) and land back here once signed in to create the meeting.
Route::get('meet/{qrToken}', MeetController::class)->middleware('auth')->name('meet');

Route::middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', SocialRedirectController::class)->name('social.redirect');
    Route::get('auth/{provider}/callback', SocialCallbackController::class)->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::inertia('scan', 'Scan')->name('scan');

    Route::post('meetings', [MeetingController::class, 'store'])
        ->middleware('throttle:meetme-scan')
        ->name('meetings.store');
    Route::get('meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::get('inbox', InboxController::class)->name('inbox');
    Route::patch('meetings/{meeting}/answer', MeetingAnswerController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('meetings.answer');
    Route::patch('meetings/{meeting}/resolve', MeetingResolveController::class)->name('meetings.resolve');
    Route::delete('meetings/{meeting}/answer', MeetingAnswerRedactionController::class)->name('meetings.answer.redact');
});

require __DIR__.'/settings.php';
