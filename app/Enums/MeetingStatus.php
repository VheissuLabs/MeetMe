<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
}
