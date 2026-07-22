<?php

use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('hides the question from the recipient while pending', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->recipient)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('meetings/Show')
            ->where('meeting.question', null)
            ->where('meeting.isInitiator', false));
});

it('shows the question to the initiator while pending', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('meeting.question', $meeting->question)
            ->where('meeting.isInitiator', true)
            ->where('meeting.otherParty.name', $meeting->recipient->name));
});

it('reveals question and answer to the recipient once answered', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('meeting.question', $meeting->question)
            ->where('meeting.answer', $meeting->answer)
            ->where('meeting.status', 'answered'));
});

it('shows the rating on confirmed meetings to both parties', function (string $role) {
    $meeting = Meeting::factory()->confirmed()->create();

    $this->actingAs($meeting->{$role})
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('meeting.status', 'confirmed')
            ->where('meeting.rating', $meeting->rating)
            ->where('meeting.answer', $meeting->answer));
})->with(['initiator', 'recipient']);

it('renders rejected meetings without a rating', function () {
    $meeting = Meeting::factory()->rejected()->create();

    $this->actingAs($meeting->initiator)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('meeting.status', 'rejected')
            ->where('meeting.rating', null));
});

it('marks redacted answers without leaking the text', function () {
    $meeting = Meeting::factory()->redacted()->create();

    $this->actingAs($meeting->initiator)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('meeting.answer', null)
            ->where('meeting.answerRedacted', true)
            ->where('meeting.rating', $meeting->rating));
});

it('forbids non-participants', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('meetings.show', $meeting))
        ->assertForbidden();

    $this->post(route('logout'));
    $this->get(route('meetings.show', $meeting))->assertRedirect(route('login', absolute: false));
});
