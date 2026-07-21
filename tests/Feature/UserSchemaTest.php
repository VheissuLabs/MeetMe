<?php

use App\Enums\AvatarSource;
use App\Models\User;
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

it('stores github identity via the factory state', function () {
    $user = User::factory()->withGithub()->create();

    expect($user->github_id)->not->toBeNull()
        ->and($user->github_username)->not->toBeNull();
});

it('stores x identity and socials via the factory states', function () {
    $user = User::factory()->withSocials()->create();

    expect($user->x_id)->not->toBeNull()
        ->and($user->x_username)->not->toBeNull()
        ->and($user->bluesky_handle)->toEndWith('.bsky.social')
        ->and($user->pronouns)->not->toBeNull();
});
