<?php

use App\Enums\MeetingStatus;
use App\Events\ActivityLogged;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;

it('logs meeting creation and status changes under the meeting log', function () {
    $meeting = Meeting::factory()->create(); // pending
    $meeting->update(['status' => MeetingStatus::Confirmed]);

    $entries = Activity::query()->where('log_name', 'meeting')->get();

    expect($entries)->toHaveCount(2)
        ->and($entries->pluck('description')->all())->toBe(['Meeting created', 'Meeting updated'])
        ->and($entries->last()->attribute_changes['attributes']['status'])->toBe(MeetingStatus::Confirmed->value);
});

it('never records answer text in the meeting activity log', function () {
    $meeting = Meeting::factory()->create();
    $meeting->update(['answer' => 'a private answer', 'status' => MeetingStatus::Answered]);

    $loggedKeys = Activity::query()->where('log_name', 'meeting')->get()
        ->flatMap(fn (Activity $a) => array_keys($a->attribute_changes['attributes'] ?? []));

    expect($loggedKeys)->not->toContain('answer');
});

it('logs profile changes under the user log without leaking secrets', function () {
    $user = User::factory()->create();
    $user->update(['pronouns' => 'they/them']);

    $activity = Activity::query()->where('log_name', 'user')->latest('id')->first();

    expect($activity->description)->toBe('User updated')
        ->and($activity->attribute_changes['attributes'])->toHaveKey('pronouns')
        ->and(array_keys($activity->attribute_changes['attributes']))->not->toContain('password');
});

it('logs question creation under the question log', function () {
    IcebreakerQuestion::factory()->create();

    expect(Activity::query()->where('log_name', 'question')->where('description', 'Question created')->count())
        ->toBe(1);
});

it('broadcasts ActivityLogged whenever an activity is recorded', function () {
    Event::fake([ActivityLogged::class]);

    User::factory()->create();

    Event::assertDispatched(ActivityLogged::class);
});
