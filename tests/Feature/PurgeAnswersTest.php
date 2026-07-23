<?php

use App\Enums\MeetingStatus;
use App\Models\Meeting;

it('does nothing when no retention window is configured', function () {
    config(['meetme.ends_at' => null, 'meetme.purge_answers_after_days' => null]);
    $meeting = Meeting::factory()->confirmed()->create();

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    expect($meeting->refresh()->answer)->not->toBeNull();
});

it('does nothing while the retention window is still open', function () {
    config([
        'meetme.ends_at' => now()->subDay()->toDateString(),
        'meetme.purge_answers_after_days' => 30,
    ]);
    $meeting = Meeting::factory()->confirmed()->create();

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    expect($meeting->refresh()->answer)->not->toBeNull();
});

it('hard-deletes answer text once the window has passed', function () {
    config([
        'meetme.ends_at' => now()->subDays(40)->toDateString(),
        'meetme.purge_answers_after_days' => 30,
    ]);
    $meeting = Meeting::factory()->confirmed()->create();
    $rating = $meeting->rating;

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    $meeting->refresh();

    expect($meeting->answer)->toBeNull()
        ->and($meeting->rating)->toBe($rating)
        ->and($meeting->status)->toBe(MeetingStatus::Confirmed);
});

it('leaves scores and meetings intact after purging', function () {
    config([
        'meetme.ends_at' => now()->subDays(40)->toDateString(),
        'meetme.purge_answers_after_days' => 30,
    ]);
    $meeting = Meeting::factory()->confirmed()->create();

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    expect(Meeting::query()->confirmed()->involving($meeting->initiator)->count())->toBe(1);
});
