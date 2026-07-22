<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->initiator_id || $user->id === $meeting->recipient_id;
    }
}
