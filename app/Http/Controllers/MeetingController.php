<?php

namespace App\Http\Controllers;

use App\Actions\SelectIcebreakerQuestion;
use App\Enums\MeetingStatus;
use App\Http\Requests\MeetingStoreRequest;
use App\Models\Meeting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MeetingController extends Controller
{
    public function store(MeetingStoreRequest $request, SelectIcebreakerQuestion $selectQuestion): RedirectResponse
    {
        $initiator = $request->user();
        $recipient = $request->recipient();

        if ($existing = Meeting::between($initiator, $recipient)) {
            return redirect()
                ->route('meetings.show', $existing)
                ->with('toast', ['type' => 'info', 'message' => __("You two have already met — here's your meeting.")]);
        }

        try {
            $meeting = Meeting::create([
                'initiator_id' => $initiator->id,
                'recipient_id' => $recipient->id,
                ...$selectQuestion->for($recipient),
            ]);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('meetings.show', Meeting::between($initiator, $recipient));
        }

        return redirect()->route('meetings.show', $meeting);
    }

    public function show(Request $request, Meeting $meeting): Response
    {
        Gate::authorize('view', $meeting);

        $isInitiator = $meeting->initiator_id === $request->user()->id;

        return Inertia::render('meetings/Show', [
            'meeting' => [
                'id' => $meeting->id,
                'status' => $meeting->status,
                // The question is the recipient's own icebreaker — revealing it
                // before it has been asked out loud spoils the game.
                'question' => $isInitiator || $meeting->status !== MeetingStatus::Pending
                    ? $meeting->question
                    : null,
                'answer' => $meeting->answer,
                'answerRedacted' => $meeting->answer_redacted_at !== null,
                'rating' => $meeting->rating,
                'isInitiator' => $isInitiator,
                'canRedact' => $request->user()->can('redactAnswer', $meeting),
                'otherParty' => $isInitiator
                    ? $meeting->recipient->only(['name', 'pronouns', 'avatar_url'])
                    : $meeting->initiator->only(['name', 'pronouns', 'avatar_url']),
            ],
        ]);
    }
}
