<?php

use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('email login records email as the last-used method', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertCookie('last_login', 'email');
});

test('github login records github as the last-used method', function () {
    Socialite::fake('github', (new SocialiteUser)->map([
        'id' => '123', 'nickname' => 'octocat', 'name' => 'Octo', 'email' => 'octo@example.com',
    ]));

    $this->get(route('social.callback', 'github'))->assertCookie('last_login', 'github');
});

test('bluesky login records bluesky as the last-used method', function () {
    Socialite::fake('bluesky', (new SocialiteUser)->map([
        'id' => 'did:plc:abc', 'nickname' => 'karl.bsky.social', 'name' => 'Karl', 'email' => null,
    ]));

    $this->get(route('social.callback', 'bluesky'))->assertCookie('last_login', 'bluesky');
});

test('the last-used method is shared with the frontend', function () {
    $this->withCookie('last_login', 'github')
        ->get(route('login'))
        ->assertInertia(fn ($page) => $page->where('lastLogin', 'github'));
});
