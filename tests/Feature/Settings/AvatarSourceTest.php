<?php

use App\Actions\ResolveAvatarUrl;
use App\Enums\AvatarSource;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('stores a gravatar avatar url on email registration', function () {
    $user = User::factory()->create(['email' => 'Karl@Example.com', 'avatar_source' => AvatarSource::Gravatar]);

    $hash = hash('sha256', 'karl@example.com');

    expect($user->avatar_url)->toBe("https://unavatar.io/gravatar/{$hash}");
});

it('resolves x avatars with a gravatar fallback', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'x_username' => 'karlbuilds',
        'avatar_source' => 'x',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->avatar_source)->toBe(AvatarSource::X)
        ->and($user->avatar_url)->toStartWith('https://unavatar.io/x/karlbuilds?fallback=')
        ->and($user->avatar_url)->toContain('gravatar');
});

it('resolves github avatars from the linked account username', function () {
    $user = User::factory()->withGithub()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_source' => 'github',
    ])->assertSessionHasNoErrors();

    $username = $user->githubAccount->username;

    expect($user->refresh()->avatar_url)->toStartWith("https://unavatar.io/github/{$username}?fallback=");
});

it('rejects the x source without an x username', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_source' => 'x',
    ])->assertSessionHasErrors('x_username');
});

it('rejects the github source without a linked github account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_source' => 'github',
    ])->assertSessionHasErrors('avatar_source');
});

it('recomputes the gravatar url when the email changes', function () {
    $user = User::factory()->create(['avatar_source' => AvatarSource::Gravatar]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'new@example.com',
    ])->assertSessionHasNoErrors();

    $hash = hash('sha256', 'new@example.com');

    expect($user->refresh()->avatar_url)->toBe("https://unavatar.io/gravatar/{$hash}");
});

it('only offers sources the user has data for', function () {
    $emailOnly = User::factory()->create();
    $withEverything = User::factory()->withGithub()->create(['x_username' => 'karlbuilds']);

    expect(array_keys(app(ResolveAvatarUrl::class)->optionsFor($emailOnly)))->toBe(['gravatar'])
        ->and(array_keys(app(ResolveAvatarUrl::class)->optionsFor($withEverything)))->toBe(['github', 'x', 'gravatar']);
});

it('shares avatar options with the profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/Profile')
            ->has('avatarOptions.gravatar')
            ->missing('avatarOptions.github')
            ->missing('avatarOptions.x'));
});
