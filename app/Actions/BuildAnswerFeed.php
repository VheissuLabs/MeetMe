<?php

namespace App\Actions;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Pagination\Paginator;

class BuildAnswerFeed
{
    /** @return Paginator<int, Meeting> */
    public function for(User $user): Paginator
    {
        $query = Meeting::query()
            ->confirmed()
            ->where('recipient_id', $user->id)
            ->whereNotNull('answer')
            ->whereNull('answer_redacted_at');

        if (! $user->answers_visible) {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->with('initiator:id,name')
            ->latest('resolved_at')
            ->simplePaginate(15);
    }
}
