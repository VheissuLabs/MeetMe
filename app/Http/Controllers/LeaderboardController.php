<?php

namespace App\Http\Controllers;

use App\Actions\GetLeaderboard;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __invoke(GetLeaderboard $leaderboard): Response
    {
        return Inertia::render('Leaderboard', [
            'conferenceName' => Event::current()->name,
            'rankings' => $leaderboard->get(),
        ]);
    }
}
