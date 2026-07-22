<?php

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;

it('lets the recipient hard-delete their answer without losing points', function () {
    $meeting = Meeting::factory()->confirmed()->create();
    $rating = $meeting->rating;

    $this->actingAs($meeting->recipient)
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    $meeting->refresh();

    expect($meeting->answer)->toBeNull()
        ->and($meeting->answer_redacted_at)->not->toBeNull()
        ->and($meeting->status)->toBe(MeetingStatus::Confirmed)
        ->and($meeting->rating)->toBe($rating);

    expect(Meeting::query()->confirmed()->involving($meeting->initiator)->count())->toBe(1)
        ->and(Meeting::query()->confirmed()->involving($meeting->recipient)->count())->toBe(1);
});

it('forbids the initiator from redacting the answer', function () {
    $meeting = Meeting::factory()->confirmed()->create();

    $this->actingAs($meeting->initiator)
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertForbidden();

    expect($meeting->refresh()->answer)->not->toBeNull();
});

it('forbids a stranger from redacting the answer', function () {
    $meeting = Meeting::factory()->confirmed()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertForbidden();
});

it('cannot redact before the meeting is confirmed', function (string $state) {
    $meeting = Meeting::factory()->{$state}()->create();

    $this->actingAs($meeting->recipient)
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertForbidden();
})->with(['answered', 'rejected']);

it('cannot redact a pending meeting', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->recipient)
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertForbidden();
});

it('is idempotent-safe: an already-redacted answer cannot be redacted again', function () {
    $meeting = Meeting::factory()->redacted()->create();

    $this->actingAs($meeting->recipient)
        ->delete(route('meetings.answer.redact', $meeting))
        ->assertForbidden();
});

it('requires authentication', function () {
    $meeting = Meeting::factory()->confirmed()->create();

    $this->delete(route('meetings.answer.redact', $meeting))
        ->assertRedirect(route('login', absolute: false));
});

it('shows the redacted placeholder instead of the answer text afterwards', function () {
    $meeting = Meeting::factory()->confirmed()->create();

    $this->actingAs($meeting->recipient)->delete(route('meetings.answer.redact', $meeting));

    $this->actingAs($meeting->initiator)
        ->get(route('meetings.show', $meeting))
        ->assertInertia(fn ($page) => $page
            ->where('meeting.answer', null)
            ->where('meeting.answerRedacted', true)
            ->where('meeting.rating', $meeting->rating));
});
