<?php

namespace App\Observers;

use App\Models\Meeting;

class MeetingObserver
{
    public function creating(Meeting $meeting): void
    {
        $meeting->pair_key ??= Meeting::pairKeyFor($meeting->initiator, $meeting->recipient);
    }
}
