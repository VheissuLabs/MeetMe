<?php

namespace App\Listeners;

use App\Enums\SocialProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

class RememberLoginMethod
{
    public function handle(Login $event): void
    {
        $provider = request()->route('provider');
        $method = $provider instanceof SocialProvider ? $provider->value : 'email';

        // Remember for a year so a returning attendee sees how they last
        // signed in. Read back as a shared Inertia prop.
        Cookie::queue('last_login', $method, 60 * 24 * 365);
    }
}
