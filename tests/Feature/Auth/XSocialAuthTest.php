<?php

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeXUser(array $overrides = []): SocialiteUser
{
    return (new SocialiteUser)->map([
        'id' => '98765',
        'nickname' => 'birdapp',
        'name' => 'Bird App',
        'email' => 'bird@example.com',
        'avatar' => 'https://pbs.twimg.com/profile_images/98765/photo.jpg',
        ...$overrides,
    ]);
}

test('the x redirect sends users to the provider', function () {
    Socialite::fake('x');

    $this->get(route('social.redirect', 'x'))->assertRedirect();
});

test('a first x login creates a passwordless user with a linked account', function () {
    Socialite::fake('x', fakeXUser());

    $response = $this->get(route('social.callback', 'x'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::query()->firstWhere('email', 'bird@example.com');

    expect($user->password)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->avatar_source)->toBe(AvatarSource::X)
        ->and($user->qr_token)->not->toBeNull()
        ->and($user->xAccount->provider)->toBe(SocialProvider::X)
        ->and($user->xAccount->provider_id)->toBe('98765')
        ->and($user->xAccount->username)->toBe('birdapp');
});

test('a returning x user logs into their existing account', function () {
    $user = User::factory()->create();
    SocialAccount::factory()->x()->create([
        'user_id' => $user->id,
        'provider_id' => '98765',
    ]);

    Socialite::fake('x', fakeXUser());

    $this->get(route('social.callback', 'x'))->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1);
});

test('an x login links to an existing user by email', function () {
    $user = User::factory()->create(['email' => 'bird@example.com']);

    Socialite::fake('x', fakeXUser());

    $this->get(route('social.callback', 'x'));

    $this->assertAuthenticatedAs($user);
    expect($user->xAccount()->first())->not->toBeNull();
});

test('an x account can coexist with a github account on one user', function () {
    $user = User::factory()->withGithub()->create(['email' => 'bird@example.com']);

    Socialite::fake('x', fakeXUser());

    $this->get(route('social.callback', 'x'));

    $this->assertAuthenticatedAs($user);
    expect($user->socialAccounts()->count())->toBe(2);
});

test('an x account without an email cannot sign in', function () {
    Socialite::fake('x', fakeXUser(['email' => null]));

    $response = $this->get(route('social.callback', 'x'));

    $response->assertRedirect(route('login', absolute: false));
    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});
