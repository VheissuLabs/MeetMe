<?php

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;

it('confirms a meeting with a rating and scores both users', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 4])
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    $meeting->refresh();

    expect($meeting->status)->toBe(MeetingStatus::Confirmed)
        ->and($meeting->rating)->toBe(4)
        ->and($meeting->resolved_at)->not->toBeNull();

    expect(Meeting::query()->confirmed()->involving($meeting->initiator)->count())->toBe(1)
        ->and(Meeting::query()->confirmed()->involving($meeting->recipient)->count())->toBe(1);
});

it('rejects a meeting without a rating', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'rejected'])
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    $meeting->refresh();

    expect($meeting->status)->toBe(MeetingStatus::Rejected)
        ->and($meeting->rating)->toBeNull()
        ->and($meeting->resolved_at)->not->toBeNull();
});

it('requires a rating when confirming', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed'])
        ->assertSessionHasErrors('rating');

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Answered);
});

it('prohibits a rating when rejecting', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'rejected', 'rating' => 5])
        ->assertSessionHasErrors('rating');

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Answered);
});

it('rejects a rating outside 1 to 5', function (int $rating) {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => $rating])
        ->assertSessionHasErrors('rating');
})->with([0, 6, -1, 100]);

it('rejects a status that is neither confirmed nor rejected', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'pending'])
        ->assertSessionHasErrors('status');
});

it('forbids the initiator from resolving', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 3])
        ->assertForbidden();

    expect($meeting->refresh()->status)->toBe(MeetingStatus::Answered);
});

it('forbids a stranger from resolving', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 3])
        ->assertForbidden();
});

it('cannot resolve a meeting that is not answered', function (string $state) {
    $meeting = Meeting::factory()->{$state}()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 3])
        ->assertForbidden();
})->with(['confirmed', 'rejected']);

it('cannot resolve a pending meeting', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 3])
        ->assertForbidden();
});

it('requires authentication', function () {
    $meeting = Meeting::factory()->answered()->create();

    $this->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 3])
        ->assertRedirect(route('login', absolute: false));
});

it('rejected pairs cannot retry because the pair_key persists', function () {
    $meeting = Meeting::factory()->rejected()->create();

    expect(Meeting::between($meeting->initiator, $meeting->recipient)?->id)->toBe($meeting->id);
});
