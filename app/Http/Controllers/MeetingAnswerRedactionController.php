<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MeetingAnswerRedactionController extends Controller
{
    public function __invoke(Request $request, Meeting $meeting): RedirectResponse
    {
        Gate::authorize('redactAnswer', $meeting);

        $meeting->update([
            'answer' => null,
            'answer_redacted_at' => now(),
        ]);

        return redirect()
            ->route('meetings.show', $meeting)
            ->with('toast', ['type' => 'success', 'message' => __('Your answer was removed. Your points are safe.')]);
    }
}
