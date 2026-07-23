<?php

namespace App\Actions;

use App\Enums\MeetingStatus;
use Illuminate\Support\Facades\DB;

class GetLeaderboard
{
    /** @return array<int, array{name: string, pronouns: string|null, avatar_url: string|null, score: int}> */
    public function get(): array
    {
        $participations = DB::table('meetings')
            ->where('status', MeetingStatus::Confirmed->value)
            ->select('initiator_id as user_id', 'resolved_at')
            ->unionAll(
                DB::table('meetings')
                    ->where('status', MeetingStatus::Confirmed->value)
                    ->select('recipient_id as user_id', 'resolved_at')
            );

        $scores = DB::query()
            ->fromSub($participations, 'participations')
            ->select('user_id', DB::raw('count(*) as score'), DB::raw('max(resolved_at) as last_confirmed_at'))
            ->groupBy('user_id');

        return DB::query()
            ->fromSub($scores, 'scores')
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->orderByDesc('scores.score')
            ->orderBy('scores.last_confirmed_at')
            ->orderBy('users.name')
            ->get(['users.name', 'users.pronouns', 'users.avatar_url', 'scores.score'])
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'pronouns' => $row->pronouns === null ? null : (string) $row->pronouns,
                'avatar_url' => $row->avatar_url === null ? null : (string) $row->avatar_url,
                'score' => (int) $row->score,
            ])
            ->all();
    }
}
