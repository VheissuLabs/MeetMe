<?php

use App\Models\Event;
use App\Models\User;

it('lets an admin reach the panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertSuccessful();
});

it('forbids a non-admin from the panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('redirects guests away from the panel', function () {
    $this->get('/admin')->assertRedirect();
});

it('seeds the current event from config defaults on first access', function () {
    config([
        'meetme.conference_name' => 'Laracon US 2026',
        'meetme.ends_at' => '2026-07-30',
    ]);

    $event = Event::current();

    expect($event->name)->toBe('Laracon US 2026')
        ->and($event->ends_at->toDateString())->toBe('2026-07-30')
        ->and(Event::query()->count())->toBe(1);
});

it('returns the same singleton event on repeat calls', function () {
    $first = Event::current();
    $second = Event::current();

    expect($first->id)->toBe($second->id)
        ->and(Event::query()->count())->toBe(1);
});

it('reflects updates on the next read', function () {
    Event::current()->update(['name' => 'Renamed Conf']);

    expect(Event::current()->name)->toBe('Renamed Conf');
});

it('always returns a real Event model even after the cache store is used', function () {
    // Regression: caching the model serialized it, and the database cache
    // returned an __PHP_Incomplete_Class on later reads.
    Event::current();
    cache()->put('unrelated', 'value');

    expect(Event::current())->toBeInstanceOf(Event::class)
        ->and(Event::current()->exists)->toBeTrue();
});

it('reads the conference name from the event on the landing and leaderboard', function () {
    Event::current()->update(['name' => 'DevConf 2026']);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('conferenceName', 'DevConf 2026'));

    $this->get(route('leaderboard'))
        ->assertInertia(fn ($page) => $page->where('conferenceName', 'DevConf 2026'));
});

it('promotes and revokes a user via the make-admin command', function () {
    $user = User::factory()->create(['email' => 'promote@example.com']);

    $this->artisan('meetme:make-admin', ['email' => 'promote@example.com'])->assertSuccessful();
    expect($user->refresh()->is_admin)->toBeTrue();

    $this->artisan('meetme:make-admin', ['email' => 'promote@example.com', '--revoke' => true])->assertSuccessful();
    expect($user->refresh()->is_admin)->toBeFalse();
});

it('fails clearly for an unknown email', function () {
    $this->artisan('meetme:make-admin', ['email' => 'nobody@example.com'])->assertFailed();
});
