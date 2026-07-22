<?php

namespace App\Http\Controllers;

use App\Enums\MeetingStatus;
use App\Http\Requests\MeetingAnswerRequest;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;

class MeetingAnswerController extends Controller
{
    public function __invoke(MeetingAnswerRequest $request, Meeting $meeting): RedirectResponse
    {
        $meeting->update([
            'answer' => $request->validated('answer'),
            'status' => MeetingStatus::Answered,
            'answered_at' => now(),
        ]);

        return redirect()
            ->route('meetings.show', $meeting)
            ->with('toast', ['type' => 'success', 'message' => __('Answer sent for confirmation.')]);
    }
}
