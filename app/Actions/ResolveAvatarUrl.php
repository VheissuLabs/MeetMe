<?php

namespace App\Actions;

use App\Enums\AvatarSource;
use App\Models\User;

class ResolveAvatarUrl
{
    public function handle(User $user, ?AvatarSource $source = null): ?string
    {
        $source ??= $user->avatar_source ?? AvatarSource::Github;

        return match ($source) {
            AvatarSource::Github => $this->github($user),
            AvatarSource::X => $this->x($user),
            AvatarSource::Gravatar => $this->gravatar($user),
        };
    }

    /** @return array<string, string> */
    public function optionsFor(User $user): array
    {
        return collect(AvatarSource::cases())
            ->mapWithKeys(fn (AvatarSource $source): array => [$source->value => $this->handle($user, $source)])
            ->filter()
            ->all();
    }

    private function github(User $user): ?string
    {
        $username = $user->githubAccount()->value('username');

        return filled($username) ? $this->unavatar('github/'.$username, $this->gravatar($user)) : null;
    }

    private function x(User $user): ?string
    {
        return filled($user->x_username) ? $this->unavatar('x/'.$user->x_username, $this->gravatar($user)) : null;
    }

    private function gravatar(User $user): string
    {
        return $this->unavatar('gravatar/'.hash('sha256', strtolower(trim($user->email))));
    }

    private function unavatar(string $path, ?string $fallback = null): string
    {
        return 'https://unavatar.io/'.$path.($fallback === null ? '' : '?fallback='.urlencode($fallback));
    }
}
