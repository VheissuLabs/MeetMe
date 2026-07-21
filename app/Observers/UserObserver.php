<?php

namespace App\Observers;

use App\Actions\ResolveAvatarUrl;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function __construct(private ResolveAvatarUrl $resolveAvatarUrl) {}

    public function creating(User $user): void
    {
        $user->qr_token ??= (string) Str::ulid();
    }

    public function saving(User $user): void
    {
        if ($user->isDirty(['avatar_source', 'x_username', 'email']) || blank($user->avatar_url)) {
            $user->avatar_url = $this->resolveAvatarUrl->handle($user) ?? $user->avatar_url;
        }
    }
}
