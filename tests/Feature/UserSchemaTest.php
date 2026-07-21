<?php

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

it('generates a qr_token when a user is created', function () {
    $user = User::factory()->create();

    expect($user->qr_token)->not->toBeNull()
        ->and(Str::isUlid($user->qr_token))->toBeTrue();
});

it('generates unique qr_tokens per user', function () {
    $users = User::factory()->count(5)->create();

    expect($users->pluck('qr_token')->unique())->toHaveCount(5);
});

it('keeps an explicitly assigned qr_token', function () {
    $token = (string) Str::ulid();

    $user = User::factory()->create(['qr_token' => $token]);

    expect($user->qr_token)->toBe($token);
});

it('allows users without a password', function () {
    $user = User::factory()->withoutPassword()->create();

    expect($user->password)->toBeNull();
});

it('defaults to a github avatar source and hidden email', function () {
    $user = User::factory()->create()->refresh();

    expect($user->avatar_source)->toBe(AvatarSource::Github)
        ->and($user->email_visible)->toBeFalse();
});

it('links a github social account via the factory state', function () {
    $user = User::factory()->withGithub()->create();

    expect($user->githubAccount)->not->toBeNull()
        ->and($user->githubAccount->provider)->toBe(SocialProvider::Github)
        ->and($user->githubAccount->provider_id)->not->toBeNull()
        ->and($user->githubAccount->username)->not->toBeNull();
});

it('links an x account and socials via the factory states', function () {
    $user = User::factory()->withSocials()->create();

    expect($user->xAccount)->not->toBeNull()
        ->and($user->xAccount->provider)->toBe(SocialProvider::X)
        ->and($user->bluesky_handle)->toEndWith('.bsky.social')
        ->and($user->pronouns)->not->toBeNull();
});

it('rejects duplicate provider identities', function () {
    $account = SocialAccount::factory()->github()->create();

    SocialAccount::factory()->github()->create([
        'provider_id' => $account->provider_id,
    ]);
})->throws(UniqueConstraintViolationException::class);

it('rejects a second account for the same provider on one user', function () {
    $user = User::factory()->withGithub()->create();

    SocialAccount::factory()->github()->create([
        'user_id' => $user->id,
    ]);
})->throws(UniqueConstraintViolationException::class);
