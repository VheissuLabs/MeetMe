<?php

use App\Enums\Achievement;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

test('an oauth user without a password can delete their account', function () {
    $user = User::factory()->withoutPassword()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('has_password flag reflects whether the account has a password', function () {
    expect(User::factory()->create()->has_password)->toBeTrue()
        ->and(User::factory()->withoutPassword()->create()->has_password)->toBeFalse();
});

test('meetme profile fields can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'pronouns' => 'she/her',
            'x_username' => 'karlbuilds',
            'bluesky_handle' => 'karl.dev',
            'linkedin_username' => 'karl-murray',
            'email_visible' => '1',
        ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->pronouns)->toBe('she/her')
        ->and($user->x_username)->toBe('karlbuilds')
        ->and($user->bluesky_handle)->toBe('karl.dev')
        ->and($user->linkedin_username)->toBe('karl-murray')
        ->and($user->email_visible)->toBeTrue();
});

test('completing every social unlocks the all-wired-up badge on save', function () {
    $user = User::factory()->withGithub()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'x_username' => 'karl',
        'bluesky_handle' => 'karl.bsky.social',
        'linkedin_username' => 'karl-murray',
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->achievements()->where('achievement', Achievement::AllWiredUp)->exists())->toBeTrue();
});

test('profile card fields can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'headline' => 'Rust-curious, coffee-powered',
        'job_title' => 'Senior Engineer',
        'company' => 'Acme',
        'answers_visible' => '1',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->headline)->toBe('Rust-curious, coffee-powered')
        ->and($user->job_title)->toBe('Senior Engineer')
        ->and($user->company)->toBe('Acme')
        ->and($user->answers_visible)->toBeTrue();
});

test('unchecking answers visibility turns it off', function () {
    $user = User::factory()->create(['answers_visible' => true]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->answers_visible)->toBeFalse();
});

test('handles are normalized before saving', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'x_username' => '@karlbuilds',
        'bluesky_handle' => '@Karl.BSKY.social',
        'linkedin_username' => 'https://www.linkedin.com/in/karl-murray/',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->x_username)->toBe('karlbuilds')
        ->and($user->bluesky_handle)->toBe('karl.bsky.social')
        ->and($user->linkedin_username)->toBe('karl-murray');
});

test('unchecking email visibility turns it off', function () {
    $user = User::factory()->create(['email_visible' => true]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->email_visible)->toBeFalse();
});

test('invalid meetme profile fields are rejected', function (array $input, string $field) {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            ...$input,
        ])
        ->assertSessionHasErrors($field);
})->with([
    'pronouns too long' => [['pronouns' => str_repeat('x', 31)], 'pronouns'],
    'x username with spaces' => [['x_username' => 'not a handle'], 'x_username'],
    'x username too long' => [['x_username' => str_repeat('a', 16)], 'x_username'],
    'bluesky without a domain' => [['bluesky_handle' => 'not-a-domain'], 'bluesky_handle'],
    'linkedin too short' => [['linkedin_username' => 'ab'], 'linkedin_username'],
    'linkedin with spaces' => [['linkedin_username' => 'not a slug'], 'linkedin_username'],
]);

test('the profile form supports precognitive validation', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->withHeaders(['Precognition' => 'true', 'Precognition-Validate-Only' => 'x_username'])
        ->patchJson(route('profile.update'), ['x_username' => 'bad handle!'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('x_username');
});

test('clearing optional fields nulls them', function () {
    $user = User::factory()->withSocials()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'pronouns' => '',
        'x_username' => '',
        'bluesky_handle' => '',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->pronouns)->toBeNull()
        ->and($user->x_username)->toBeNull()
        ->and($user->bluesky_handle)->toBeNull();
});
