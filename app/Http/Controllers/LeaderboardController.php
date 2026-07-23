<?php

namespace App\Http\Controllers;

use App\Actions\GetLeaderboard;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __invoke(GetLeaderboard $leaderboard): Response
    {
        return Inertia::render('Leaderboard', [
            'conferenceName' => config('meetme.conference_name'),
            'rankings' => $leaderboard->get(),
        ]);
    }
}
