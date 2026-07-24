<?php

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Spatie\Activitylog\Models\Activity;

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

it('records the purge in the activity log with the count', function () {
    config([
        'meetme.ends_at' => now()->subDays(40)->toDateString(),
        'meetme.purge_answers_after_days' => 30,
    ]);
    Meeting::factory()->confirmed()->count(2)->create();

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    $activity = Activity::query()->where('log_name', 'admin')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Purged 2 recorded answers');
});

it('does not log when nothing is purged', function () {
    config(['meetme.ends_at' => null, 'meetme.purge_answers_after_days' => null]);
    Meeting::factory()->confirmed()->create();

    $this->artisan('meetme:purge-answers')->assertSuccessful();

    expect(Activity::query()->where('log_name', 'admin')->count())->toBe(0);
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
