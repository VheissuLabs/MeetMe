<?php

namespace App\Http\Controllers;

use App\Enums\MeetingStatus;
use App\Http\Requests\MeetingResolveRequest;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;

class MeetingResolveController extends Controller
{
    public function __invoke(MeetingResolveRequest $request, Meeting $meeting): RedirectResponse
    {
        $status = $request->status();

        $meeting->update([
            'status' => $status,
            'rating' => $status === MeetingStatus::Confirmed ? (int) $request->validated('rating') : null,
            'resolved_at' => now(),
        ]);

        return redirect()
            ->route('meetings.show', $meeting)
            ->with('toast', $status === MeetingStatus::Confirmed
                ? ['type' => 'success', 'message' => __('Confirmed — you both scored a point!')]
                : ['type' => 'info', 'message' => __('Meeting rejected.')]);
    }
}
