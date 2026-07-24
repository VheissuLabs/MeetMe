<?php

use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the profile of someone you have a confirmed meeting with', function () {
    $me = User::factory()->create();
    $them = User::factory()->create(['name' => 'Ada', 'linkedin_username' => 'ada-dev']);
    Meeting::factory()->confirmed()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('UserProfile')
            ->where('profile.name', 'Ada')
            ->where('profile.socials.linkedin', 'https://www.linkedin.com/in/ada-dev'));
});

it('404s for a user you have never met', function () {
    $me = User::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($me)
        ->get(route('users.show', $stranger->qr_token))
        ->assertNotFound();
});

it('404s when the meeting exists but is not confirmed', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    Meeting::factory()->answered()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertNotFound();
});

it('hides the email unless the connection opted in', function () {
    $me = User::factory()->create();
    $them = User::factory()->create(['email' => 'ada@example.com', 'email_visible' => false]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $me->id, 'initiator_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page->missing('profile.email'));
});

it('keeps redacted answers redacted on the profile', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    $meeting = Meeting::factory()->redacted()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('profile.answer', null)
            ->where('profile.answerRedacted', true)
            ->where('profile.rating', $meeting->rating));
});

it('requires authentication', function () {
    $them = User::factory()->create();

    $this->get(route('users.show', $them->qr_token))->assertRedirect(route('login'));
});
