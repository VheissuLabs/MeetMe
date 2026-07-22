<?php

namespace App\Http\Controllers;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $meetings = Meeting::query()
            ->where('recipient_id', $request->user()->id)
            ->where('status', MeetingStatus::Answered)
            ->with('initiator:id,name,pronouns,avatar_url')
            ->latest('answered_at')
            ->get()
            ->map(fn (Meeting $meeting): array => [
                'id' => $meeting->id,
                'question' => $meeting->question,
                'answer' => $meeting->answer,
                'answered_at' => $meeting->answered_at?->toIso8601String(),
                'initiator' => $meeting->initiator->only(['name', 'pronouns', 'avatar_url']),
            ]);

        return response()->json(['meetings' => $meetings]);
    }
}
