<?php

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Str;

it('creates a meeting for an authenticated visitor and redirects to it', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();

    $this->actingAs($initiator)
        ->get(route('meet', $recipient->qr_token))
        ->assertRedirect(route('meetings.show', Meeting::query()->sole(), absolute: false));

    $meeting = Meeting::query()->sole();

    expect($meeting->initiator_id)->toBe($initiator->id)
        ->and($meeting->recipient_id)->toBe($recipient->id)
        ->and($meeting->question)->not->toBeEmpty();
});

it('redirects a duplicate deep-link scan to the existing meeting', function () {
    $one = User::factory()->create();
    $two = User::factory()->create();
    $meeting = Meeting::factory()->create(['initiator_id' => $one->id, 'recipient_id' => $two->id]);

    $this->actingAs($one)
        ->get(route('meet', $two->qr_token))
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    expect(Meeting::query()->count())->toBe(1);
});

it('bounces a self-scan back to the dashboard without creating a meeting', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('meet', $user->qr_token))
        ->assertRedirect(route('dashboard', absolute: false));

    expect(Meeting::query()->count())->toBe(0);
});

it('handles an unknown token gracefully for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('meet', (string) Str::ulid()))
        ->assertRedirect(route('dashboard', absolute: false));

    expect(Meeting::query()->count())->toBe(0);
});

it('stashes the deep-link as the intended url and sends guests to login', function () {
    $recipient = User::factory()->create();

    $this->get(route('meet', $recipient->qr_token))
        ->assertRedirect(route('login'));

    expect(session()->get('url.intended'))->toBe(route('meet', $recipient->qr_token));
});

it('creates the pending meeting once the guest logs in and follows the intended url', function () {
    $recipient = User::factory()->create();
    $newcomer = User::factory()->create(['password' => Hash::make('secret-password')]);

    // Guest scans → bounced to login, intended stashed.
    $this->get(route('meet', $recipient->qr_token))->assertRedirect(route('login'));

    // They authenticate; Fortify honors the intended URL.
    $this->post(route('login.store'), ['email' => $newcomer->email, 'password' => 'secret-password'])
        ->assertRedirect(route('meet', $recipient->qr_token));

    // Following it creates the meeting.
    $this->get(route('meet', $recipient->qr_token))
        ->assertRedirect(route('meetings.show', Meeting::query()->sole(), absolute: false));

    expect(Meeting::query()->sole()->recipient_id)->toBe($recipient->id);
});
