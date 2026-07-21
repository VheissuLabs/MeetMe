<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialRedirectController extends Controller
{
    public function __invoke(SocialProvider $provider): RedirectResponse
    {
        return Socialite::driver($provider->value)->redirect();
    }
}
