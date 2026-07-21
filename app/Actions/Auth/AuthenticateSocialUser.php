<?php

namespace App\Actions\Auth;

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticateSocialUser
{
    /**
     * Resolve the local user for an OAuth callback: match the provider
     * identity, else link by email, else create a passwordless user.
     */
    public function handle(SocialProvider $provider, SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            $account = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($account) {
                $account->update([
                    'username' => $socialUser->getNickname(),
                    'avatar_url' => $socialUser->getAvatar(),
                ]);

                return $account->user;
            }

            $user = User::query()->firstWhere('email', $socialUser->getEmail())
                ?? User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'email_verified_at' => now(),
                    'avatar_url' => $socialUser->getAvatar(),
                    'avatar_source' => $this->avatarSourceFor($provider),
                ]);

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'username' => $socialUser->getNickname(),
                'avatar_url' => $socialUser->getAvatar(),
            ]);

            return $user;
        });
    }

    private function avatarSourceFor(SocialProvider $provider): AvatarSource
    {
        return match ($provider) {
            SocialProvider::Github => AvatarSource::Github,
            SocialProvider::X => AvatarSource::X,
        };
    }
}
