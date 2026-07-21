<?php

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeGithubUser(array $overrides = []): SocialiteUser
{
    return (new SocialiteUser)->map([
        'id' => '12345',
        'nickname' => 'octocat',
        'name' => 'Octo Cat',
        'email' => 'octo@example.com',
        'avatar' => 'https://avatars.githubusercontent.com/u/12345',
        ...$overrides,
    ]);
}

test('the github redirect sends users to the provider', function () {
    Socialite::fake('github');

    $this->get(route('social.redirect', 'github'))->assertRedirect();
});

test('an unknown provider is a 404', function () {
    $this->get('/auth/facebook/redirect')->assertNotFound();
});

test('a first github login creates a passwordless user with a linked account', function () {
    Socialite::fake('github', fakeGithubUser());

    $response = $this->get(route('social.callback', 'github'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::query()->firstWhere('email', 'octo@example.com');

    expect($user->password)->toBeNull()
        ->and($user->name)->toBe('Octo Cat')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->avatar_source)->toBe(AvatarSource::Github)
        ->and($user->qr_token)->not->toBeNull()
        ->and($user->githubAccount->provider_id)->toBe('12345')
        ->and($user->githubAccount->username)->toBe('octocat');
});

test('a returning github user logs into their existing account', function () {
    $user = User::factory()->create();
    SocialAccount::factory()->github()->create([
        'user_id' => $user->id,
        'provider_id' => '12345',
        'username' => 'old-name',
    ]);

    Socialite::fake('github', fakeGithubUser());

    $this->get(route('social.callback', 'github'))->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1)
        ->and($user->githubAccount()->first()->username)->toBe('octocat');
});

test('a github login links to an existing user by email', function () {
    $user = User::factory()->create(['email' => 'octo@example.com']);

    Socialite::fake('github', fakeGithubUser());

    $this->get(route('social.callback', 'github'));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1)
        ->and($user->githubAccount()->first())->not->toBeNull()
        ->and(SocialAccount::query()->count())->toBe(1);
});

test('a github account without an email cannot sign in', function () {
    Socialite::fake('github', fakeGithubUser(['email' => null]));

    $response = $this->get(route('social.callback', 'github'));

    $response->assertRedirect(route('login', absolute: false));
    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

test('the login and register pages expose the github button', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
});

test('social accounts store the provider as an enum', function () {
    Socialite::fake('github', fakeGithubUser());

    $this->get(route('social.callback', 'github'));

    expect(SocialAccount::query()->sole()->provider)->toBe(SocialProvider::Github);
});
