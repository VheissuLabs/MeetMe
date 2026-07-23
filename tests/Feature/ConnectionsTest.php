<?php

use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('lists confirmed meetings involving the user, newest first', function () {
    $user = User::factory()->create();
    $older = Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'resolved_at' => now()->subHour()]);
    $newer = Meeting::factory()->confirmed()->create(['recipient_id' => $user->id, 'resolved_at' => now()]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Connections')
            ->has('connections', 2)
            ->where('connections.0.meeting_id', $newer->id)
            ->where('connections.1.meeting_id', $older->id));
});

it('excludes non-confirmed meetings', function () {
    $user = User::factory()->create();
    Meeting::factory()->create(['initiator_id' => $user->id]); // pending
    Meeting::factory()->answered()->create(['recipient_id' => $user->id]);
    Meeting::factory()->rejected()->create(['initiator_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('connections', 0));
});

it('shows the other party, not the current user', function () {
    $user = User::factory()->create(['name' => 'Me']);
    $them = User::factory()->create(['name' => 'Them']);
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'recipient_id' => $them->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('connections.0.name', 'Them'));
});

it('omits the email field entirely when the connection has not opted in', function () {
    $user = User::factory()->create();
    $them = User::factory()->create(['email_visible' => false]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'recipient_id' => $them->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page->missing('connections.0.email'));
});

it('includes the email only when the connection opted in', function () {
    $user = User::factory()->create();
    $them = User::factory()->create(['email' => 'them@example.com', 'email_visible' => true]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id, 'initiator_id' => $them->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('connections.0.email', 'them@example.com'));
});

it('renders only the social links that exist', function () {
    $user = User::factory()->create();
    $them = User::factory()->withGithub()->create(['x_username' => 'themonx', 'bluesky_handle' => null]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'recipient_id' => $them->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('connections.0.socials.x', 'https://x.com/themonx')
            ->has('connections.0.socials.github')
            ->missing('connections.0.socials.bluesky'));
});

it('marks redacted answers without leaking the text', function () {
    $user = User::factory()->create();
    $meeting = Meeting::factory()->redacted()->create(['recipient_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('connections'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('connections.0.answer', null)
            ->where('connections.0.answerRedacted', true)
            ->where('connections.0.rating', $meeting->rating));
});

it('requires authentication', function () {
    $this->get(route('connections'))->assertRedirect(route('login'));
});
