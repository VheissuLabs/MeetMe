<?php

arch()->preset()->laravel();

arch()->preset()->security();

arch('broadcasting originates in observers, never controllers')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'App\Events\MeetingAwaitingConfirmation',
        'App\Events\MeetingResolved',
        'App\Events\LeaderboardChanged',
        'Illuminate\Support\Facades\Broadcast',
        'broadcast',
    ]);
