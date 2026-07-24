<?php

namespace App\Http\Controllers;

use App\Actions\BuildConnections;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    public function __invoke(Request $request, User $user, BuildConnections $buildConnections): Response
    {
        $viewer = $request->user();
        $meeting = Meeting::between($viewer, $user);
        $hasMet = $meeting !== null && $meeting->status === MeetingStatus::Confirmed;

        return Inertia::render('UserProfile', [
            // Anyone at the event can view a profile; the shared meeting (their
            // icebreaker answer + rating) only shows once you've confirmed one.
            'profile' => $hasMet
                ? $buildConnections->forMeeting($meeting, $viewer)
                : $buildConnections->person($user),
            'achievements' => $user->achievements()->orderBy('earned_at')->get()
                ->map(fn (UserAchievement $earned): array => [
                    'key' => $earned->achievement->value,
                    'name' => $earned->achievement->label(),
                    'icon' => $earned->achievement->icon(),
                ])
                ->all(),
        ]);
    }
}
