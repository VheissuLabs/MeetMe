<?php

use App\Events\LeaderboardChanged;
use App\Events\MeetingAwaitingConfirmation;
use App\Events\MeetingResolved;
use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;

it('broadcasts to the recipient when an answer is submitted', function () {
    Event::fake([MeetingAwaitingConfirmation::class, MeetingResolved::class]);
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => 'They said hi.']);

    Event::assertDispatched(MeetingAwaitingConfirmation::class, function ($event) use ($meeting) {
        $channels = $event->broadcastOn();

        return $event->meeting->is($meeting)
            && $channels[0] instanceof PrivateChannel
            && $channels[0]->name === 'private-App.Models.User.'.$meeting->recipient_id;
    });
    Event::assertNotDispatched(MeetingResolved::class);
});

it('broadcasts to the initiator when a meeting is confirmed', function () {
    Event::fake([MeetingAwaitingConfirmation::class, MeetingResolved::class]);
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 5]);

    Event::assertDispatched(MeetingResolved::class, function ($event) use ($meeting) {
        $channels = $event->broadcastOn();

        return $event->meeting->is($meeting)
            && $channels[0]->name === 'private-App.Models.User.'.$meeting->initiator_id
            && $event->broadcastWith()['status'] === 'confirmed';
    });
});

it('broadcasts to the initiator when a meeting is rejected', function () {
    Event::fake([MeetingResolved::class]);
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'rejected']);

    Event::assertDispatched(MeetingResolved::class, fn ($event) => $event->broadcastWith()['status'] === 'rejected');
});

it('does not broadcast when creating a meeting', function () {
    Event::fake([MeetingAwaitingConfirmation::class, MeetingResolved::class]);

    Meeting::factory()->create();

    Event::assertNotDispatched(MeetingAwaitingConfirmation::class);
    Event::assertNotDispatched(MeetingResolved::class);
});

it('does not rebroadcast on a no-op update', function () {
    Event::fake([MeetingResolved::class]);
    $meeting = Meeting::factory()->confirmed()->create();

    $meeting->update(['rating' => 2]);

    Event::assertNotDispatched(MeetingResolved::class);
});

it('both events implement ShouldBroadcast', function () {
    expect(new MeetingAwaitingConfirmation(Meeting::factory()->create()))
        ->toBeInstanceOf(ShouldBroadcast::class)
        ->and(new MeetingResolved(Meeting::factory()->create()))
        ->toBeInstanceOf(ShouldBroadcast::class);
});

it('broadcasts a payload-free LeaderboardChanged on the public channel when confirmed', function () {
    Event::fake([LeaderboardChanged::class]);
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'confirmed', 'rating' => 5]);

    Event::assertDispatched(LeaderboardChanged::class, function ($event) {
        $channels = $event->broadcastOn();

        return $channels[0] instanceof Channel
            && ! $channels[0] instanceof PrivateChannel
            && $channels[0]->name === 'leaderboard'
            && $event->broadcastAs() === 'LeaderboardChanged';
    });
});

it('does not touch the leaderboard when a meeting is rejected', function () {
    Event::fake([LeaderboardChanged::class]);
    $meeting = Meeting::factory()->answered()->create();

    $this->actingAs($meeting->recipient)
        ->patch(route('meetings.resolve', $meeting), ['status' => 'rejected']);

    Event::assertNotDispatched(LeaderboardChanged::class);
});

it('does not broadcast LeaderboardChanged when a meeting is merely answered', function () {
    Event::fake([LeaderboardChanged::class]);
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)
        ->patch(route('meetings.answer', $meeting), ['answer' => 'A great answer.']);

    Event::assertNotDispatched(LeaderboardChanged::class);
});
