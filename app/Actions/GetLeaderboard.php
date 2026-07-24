<?php

namespace App\Actions;

use App\Enums\MeetingStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GetLeaderboard
{
    /** @return array<int, array{name: string, pronouns: string|null, avatar_url: string|null, qr_token: string, score: int}> */
    public function get(): array
    {
        return DB::query()
            ->fromSub($this->scores(), 'scores')
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->orderByDesc('scores.score')
            ->orderBy('scores.last_confirmed_at')
            ->orderBy('users.name')
            ->get(['users.name', 'users.pronouns', 'users.avatar_url', 'users.qr_token', 'scores.score'])
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'pronouns' => $row->pronouns === null ? null : (string) $row->pronouns,
                'avatar_url' => $row->avatar_url === null ? null : (string) $row->avatar_url,
                'qr_token' => (string) $row->qr_token,
                'score' => (int) $row->score,
            ])
            ->all();
    }

    /** @return array<int, array{name: string, pronouns: string|null, avatar_url: string|null, qr_token: string, average: float, rating_count: int}> */
    public function conversationalists(): array
    {
        return DB::query()
            ->fromSub($this->answerRatings(), 'ratings')
            ->join('users', 'users.id', '=', 'ratings.user_id')
            ->orderByDesc('ratings.average')
            ->orderByDesc('ratings.rating_count')
            ->orderBy('users.name')
            ->get(['users.name', 'users.pronouns', 'users.avatar_url', 'users.qr_token', 'ratings.average', 'ratings.rating_count'])
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'pronouns' => $row->pronouns === null ? null : (string) $row->pronouns,
                'avatar_url' => $row->avatar_url === null ? null : (string) $row->avatar_url,
                'qr_token' => (string) $row->qr_token,
                'average' => round((float) $row->average, 1),
                'rating_count' => (int) $row->rating_count,
            ])
            ->all();
    }

    /** @return array<int, int> */
    public function positions(): array
    {
        return DB::query()
            ->fromSub($this->scores(), 'scores')
            ->orderByDesc('scores.score')
            ->orderBy('scores.last_confirmed_at')
            ->pluck('user_id')
            ->values()
            ->mapWithKeys(fn (mixed $userId, int $index): array => [(int) $userId => $index + 1])
            ->all();
    }

    private function scores(): Builder
    {
        $participations = DB::table('meetings')
            ->where('status', MeetingStatus::Confirmed->value)
            ->select('initiator_id as user_id', 'resolved_at')
            ->unionAll(
                DB::table('meetings')
                    ->where('status', MeetingStatus::Confirmed->value)
                    ->select('recipient_id as user_id', 'resolved_at')
            );

        return DB::query()
            ->fromSub($participations, 'participations')
            ->select('user_id', DB::raw('count(*) as score'), DB::raw('max(resolved_at) as last_confirmed_at'))
            ->groupBy('user_id');
    }

    private function answerRatings(): Builder
    {
        return DB::table('meetings')
            ->where('status', MeetingStatus::Confirmed->value)
            ->whereNotNull('rating')
            ->select(
                'initiator_id as user_id',
                DB::raw('avg(rating) as average'),
                DB::raw('count(*) as rating_count'),
            )
            ->groupBy('initiator_id');
    }
}
