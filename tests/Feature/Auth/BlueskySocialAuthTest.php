<?php

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeBlueskyUser(array $overrides = []): SocialiteUser
{
    return (new SocialiteUser)->map([
        'id' => 'did:plc:abc123',
        'nickname' => 'karl.bsky.social',
        'name' => 'Karl',
        'email' => null,
        'avatar' => 'https://cdn.bsky.app/avatar/karl.jpg',
        ...$overrides,
    ]);
}

test('the bluesky redirect sends users to the provider', function () {
    Socialite::fake('bluesky');

    $this->get(route('social.redirect', 'bluesky'))->assertRedirect();
});

test('a first bluesky login creates an emailless user with a linked account', function () {
    Socialite::fake('bluesky', fakeBlueskyUser());

    $response = $this->get(route('social.callback', 'bluesky'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::query()->sole();

    expect($user->email)->toBeNull()
        ->and($user->password)->toBeNull()
        ->and($user->name)->toBe('Karl')
        ->and($user->avatar_source)->toBe(AvatarSource::Bluesky)
        ->and($user->bluesky_handle)->toBe('karl.bsky.social')
        ->and($user->qr_token)->not->toBeNull();

    expect(SocialAccount::query()->sole())
        ->provider->toBe(SocialProvider::Bluesky)
        ->provider_id->toBe('did:plc:abc123');
});

test('a returning bluesky user logs into their existing account', function () {
    $user = User::factory()->create();
    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => SocialProvider::Bluesky,
        'provider_id' => 'did:plc:abc123',
    ]);

    Socialite::fake('bluesky', fakeBlueskyUser());

    $this->get(route('social.callback', 'bluesky'))->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1);
});

test('bluesky resolves the avatar from the handle', function () {
    Socialite::fake('bluesky', fakeBlueskyUser());

    $this->get(route('social.callback', 'bluesky'));

    expect(User::query()->sole()->avatar_url)->toContain('unavatar.io/bluesky/karl.bsky.social');
});

test('two emailless bluesky users do not collide', function () {
    Socialite::fake('bluesky', fakeBlueskyUser());
    $this->get(route('social.callback', 'bluesky'));
    auth()->logout();

    Socialite::fake('bluesky', fakeBlueskyUser(['id' => 'did:plc:xyz789', 'nickname' => 'other.bsky.social']));
    $this->get(route('social.callback', 'bluesky'));

    expect(User::query()->count())->toBe(2)
        ->and(User::query()->whereNull('email')->count())->toBe(2);
});
