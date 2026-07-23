<?php

namespace App\Actions;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class RecordScan
{
    public function __construct(private SelectIcebreakerQuestion $selectQuestion) {}

    public function between(User $initiator, User $recipient): Meeting
    {
        if ($existing = Meeting::between($initiator, $recipient)) {
            return $existing;
        }

        try {
            return Meeting::create([
                'initiator_id' => $initiator->id,
                'recipient_id' => $recipient->id,
                ...$this->selectQuestion->for($recipient),
            ]);
        } catch (UniqueConstraintViolationException) {
            return Meeting::between($initiator, $recipient);
        }
    }
}
