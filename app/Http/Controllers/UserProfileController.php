<?php

namespace App\Http\Controllers;

use App\Actions\BuildConnections;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    public function __invoke(Request $request, User $user, BuildConnections $buildConnections): Response
    {
        $viewer = $request->user();
        $meeting = Meeting::between($viewer, $user);

        abort_if($meeting === null || $meeting->status !== MeetingStatus::Confirmed, 404);

        return Inertia::render('UserProfile', [
            'profile' => $buildConnections->forMeeting($meeting, $viewer),
        ]);
    }
}
