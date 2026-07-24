<?php

namespace App\Http\Controllers;

use App\Actions\GetLeaderboard;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request, GetLeaderboard $leaderboard): Response
    {
        // Names link to profiles for signed-in attendees. The public TV view
        // has no one to click and no session, so we don't expose qr_tokens there.
        $linkable = $request->user() !== null;

        return Inertia::render('Leaderboard', [
            'conferenceName' => Event::current()->name,
            'rankings' => $this->tokens($leaderboard->get(), $linkable),
            'conversationalists' => $this->tokens($leaderboard->conversationalists(), $linkable),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function tokens(array $rows, bool $linkable): array
    {
        if ($linkable) {
            return $rows;
        }

        return array_map(fn (array $row): array => Arr::except($row, 'qr_token'), $rows);
    }
}
