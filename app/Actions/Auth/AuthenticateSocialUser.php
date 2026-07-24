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

            $user = $this->linkByEmail($socialUser) ?? User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'email_verified_at' => $socialUser->getEmail() === null ? null : now(),
                'avatar_url' => $socialUser->getAvatar(),
                'avatar_source' => $this->avatarSourceFor($provider),
            ]);

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'username' => $socialUser->getNickname(),
                'avatar_url' => $socialUser->getAvatar(),
            ]);

            $this->backfillHandle($provider, $user, $socialUser);

            return $user;
        });
    }

    private function linkByEmail(SocialiteUser $socialUser): ?User
    {
        // Bluesky and other providers may not share an email; only match by
        // email when the provider actually gave us one.
        return $socialUser->getEmail() === null
            ? null
            : User::query()->firstWhere('email', $socialUser->getEmail());
    }

    private function backfillHandle(SocialProvider $provider, User $user, SocialiteUser $socialUser): void
    {
        match ($provider) {
            SocialProvider::X => blank($user->x_username)
                ? $user->update(['x_username' => $socialUser->getNickname()])
                : null,
            SocialProvider::Bluesky => blank($user->bluesky_handle)
                ? $user->update(['bluesky_handle' => $socialUser->getNickname()])
                : null,
            default => null,
        };
    }

    private function avatarSourceFor(SocialProvider $provider): AvatarSource
    {
        return match ($provider) {
            SocialProvider::Github => AvatarSource::Github,
            SocialProvider::X => AvatarSource::X,
            SocialProvider::Bluesky => AvatarSource::Bluesky,
        };
    }
}
