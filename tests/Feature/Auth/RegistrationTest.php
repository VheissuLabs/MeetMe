<?php

use App\Enums\AvatarSource;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('email registration provisions meetme defaults', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->firstWhere('email', 'test@example.com');

    expect($user->qr_token)->not->toBeNull()
        ->and(Str::isUlid($user->qr_token))->toBeTrue()
        ->and($user->avatar_source)->toBe(AvatarSource::Gravatar)
        ->and($user->email_visible)->toBeFalse();
});
