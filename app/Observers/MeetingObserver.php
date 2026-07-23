<?php

namespace App\Observers;

use App\Enums\MeetingStatus;
use App\Events\LeaderboardChanged;
use App\Events\MeetingAwaitingConfirmation;
use App\Events\MeetingResolved;
use App\Models\Meeting;

class MeetingObserver
{
    public function creating(Meeting $meeting): void
    {
        $meeting->pair_key ??= Meeting::pairKeyFor($meeting->initiator, $meeting->recipient);
    }

    public function updated(Meeting $meeting): void
    {
        if (! $meeting->wasChanged('status')) {
            return;
        }

        match ($meeting->status) {
            MeetingStatus::Answered => MeetingAwaitingConfirmation::dispatch($meeting),
            MeetingStatus::Confirmed, MeetingStatus::Rejected => MeetingResolved::dispatch($meeting),
            default => null,
        };

        if ($meeting->status === MeetingStatus::Confirmed) {
            LeaderboardChanged::dispatch();
        }
    }
}
