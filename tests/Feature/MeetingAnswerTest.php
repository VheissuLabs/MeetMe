<?php

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;

it('lets the initiator record an answer and moves the meeting to answered', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => 'They said the cursed bug was a timezone off-by-one.'])
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    $meeting->refresh();

    expect($meeting->status)->toBe(MeetingStatus::Answered)
        ->and($meeting->answer)->toBe('They said the cursed bug was a timezone off-by-one.')
        ->and($meeting->answered_at)->not->toBeNull();
});

it('forbids the recipient from answering', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.answer', $meeting), ['answer' => 'Sneaky.'])
        ->assertForbidden();

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Pending);
});

it('forbids a stranger from answering', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('meetings.answer', $meeting), ['answer' => 'Not mine.'])
        ->assertForbidden();
});

it('cannot answer a meeting that is no longer pending', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => 'Answering twice.'])
        ->assertForbidden();
});

it('requires a non-empty answer', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => ''])
        ->assertSessionHasErrors('answer');

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Pending);
});

it('rejects an answer longer than the limit', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => str_repeat('a', 2001)])
        ->assertSessionHasErrors('answer');
});

it('supports precognitive validation of the answer field', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->withHeaders(['Precognition' => 'true', 'Precognition-Validate-Only' => 'answer'])
        ->patchJson(route('meetings.answer', $meeting), ['answer' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('answer');

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Pending);
});

it('requires authentication', function () {
    $meeting = Meeting::factory()->create();

    $this->patch(route('meetings.answer', $meeting), ['answer' => 'Guest.'])
        ->assertRedirect(route('login', absolute: false));
});
