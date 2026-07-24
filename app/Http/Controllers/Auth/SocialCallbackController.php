<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateSocialUser;
use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialCallbackController extends Controller
{
    public function __invoke(SocialProvider $provider, AuthenticateSocialUser $authenticate): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider->value)->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('error', __('We could not sign you in. Please try again.'));
        }

        Auth::login($authenticate->handle($provider, $socialUser), remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
