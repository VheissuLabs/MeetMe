<?php

namespace App\Http\Controllers;

use App\Actions\RecordScan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MeetController extends Controller
{
    public function __invoke(Request $request, string $qrToken, RecordScan $recordScan): RedirectResponse
    {
        $recipient = User::query()->firstWhere('qr_token', $qrToken);

        if ($recipient === null) {
            return redirect()
                ->route('dashboard')
                ->with('toast', ['type' => 'error', 'message' => __("That code doesn't belong to anyone here.")]);
        }

        if ($request->user()->is($recipient)) {
            return redirect()
                ->route('dashboard')
                ->with('toast', ['type' => 'info', 'message' => __("That's your own code — find someone new to meet!")]);
        }

        $meeting = $recordScan->between($request->user(), $recipient);

        return redirect()->route('meetings.show', $meeting);
    }
}
