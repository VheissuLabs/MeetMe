<?php

namespace App\Actions;

use App\Models\Meeting;
use App\Models\User;

class BuildConnections
{
    /** @return array<int, array<string, mixed>> */
    public function for(User $user): array
    {
        return Meeting::query()
            ->confirmed()
            ->involving($user)
            ->with(['initiator.githubAccount', 'recipient.githubAccount'])
            ->latest('resolved_at')
            ->get()
            ->map(fn (Meeting $meeting): array => $this->connection(
                $meeting,
                $meeting->initiator_id === $user->id ? $meeting->recipient : $meeting->initiator,
            ))
            ->all();
    }

    /** @return array<string, mixed> */
    private function connection(Meeting $meeting, User $person): array
    {
        return [
            'meeting_id' => $meeting->id,
            'name' => $person->name,
            'pronouns' => $person->pronouns,
            'avatar_url' => $person->avatar_url,
            'socials' => array_filter([
                'github' => $person->githubAccount?->username
                    ? 'https://github.com/'.$person->githubAccount->username
                    : null,
                'x' => $person->x_username ? 'https://x.com/'.$person->x_username : null,
                'bluesky' => $person->bluesky_handle ? 'https://bsky.app/profile/'.$person->bluesky_handle : null,
            ]),
            ...$person->email_visible ? ['email' => $person->email] : [],
            'question' => $meeting->question,
            'answer' => $meeting->answer,
            'answerRedacted' => $meeting->answer_redacted_at !== null,
            'rating' => $meeting->rating,
        ];
    }
}
