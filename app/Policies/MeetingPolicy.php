<?php

namespace App\Policies;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->initiator_id || $user->id === $meeting->recipient_id;
    }

    public function answer(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->initiator_id && $meeting->status === MeetingStatus::Pending;
    }

    public function resolve(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->recipient_id && $meeting->status === MeetingStatus::Answered;
    }

    public function redactAnswer(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->recipient_id
            && $meeting->status === MeetingStatus::Confirmed
            && $meeting->answer !== null;
    }
}
