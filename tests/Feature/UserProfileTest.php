<?php

use App\Enums\Achievement;
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

it('shows the viewed user\'s earned achievements', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);
    $them->achievements()->create(['achievement' => Achievement::FirstConnection, 'earned_at' => now()]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('achievements', 1)
            ->where('achievements.0.key', Achievement::FirstConnection->value)
            ->where('achievements.0.icon', Achievement::FirstConnection->icon()));
});

it('shows a plain profile for someone you have never met, without meeting context', function () {
    $me = User::factory()->create();
    $stranger = User::factory()->create(['name' => 'Grace', 'x_username' => 'grace']);

    $this->actingAs($me)
        ->get(route('users.show', $stranger->qr_token))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('UserProfile')
            ->where('profile.name', 'Grace')
            ->where('profile.socials.x', 'https://x.com/grace')
            ->missing('profile.question')
            ->missing('profile.meeting_id'));
});

it('omits meeting context until the meeting is confirmed', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    Meeting::factory()->answered()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->missing('profile.question'));
});

it('includes meeting context once you have a confirmed meeting', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $me->id, 'recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('profile.question'));
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

it('includes identity fields and connection stats', function () {
    $me = User::factory()->create();
    $them = User::factory()->create(['headline' => 'Rust curious', 'job_title' => 'Engineer', 'company' => 'Acme']);
    Meeting::factory()->confirmed()->create(['recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('profile.headline', 'Rust curious')
            ->where('profile.job_title', 'Engineer')
            ->where('profile.company', 'Acme')
            ->where('stats.connections', 1));
});

it('marks your own profile as yours', function () {
    $me = User::factory()->create();

    $this->actingAs($me)
        ->get(route('users.show', $me->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('isSelf', true));
});

it('lists the icebreakers a person was asked and answered, newest first', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    $jordan = User::factory()->create(['name' => 'Jordan']);

    Meeting::factory()->confirmed()->create([
        'initiator_id' => $jordan->id,
        'recipient_id' => $them->id,
        'question' => 'Tabs or spaces?',
        'answer' => 'Spaces, obviously.',
    ]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('feed.data', 1)
            ->where('feed.data.0.asker', 'Jordan')
            ->where('feed.data.0.question', 'Tabs or spaces?')
            ->where('feed.data.0.answer', 'Spaces, obviously.'));
});

it('excludes redacted answers from the feed', function () {
    $me = User::factory()->create();
    $them = User::factory()->create();
    Meeting::factory()->redacted()->create(['recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('feed.data', 0));
});

it('hides the feed when the person opted out', function () {
    $me = User::factory()->create();
    $them = User::factory()->create(['answers_visible' => false]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $them->id]);

    $this->actingAs($me)
        ->get(route('users.show', $them->qr_token))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('feed.data', 0));
});

it('requires authentication', function () {
    $them = User::factory()->create();

    $this->get(route('users.show', $them->qr_token))->assertRedirect(route('login'));
});
