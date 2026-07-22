<?php

namespace App\Actions;

use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Arr;

class SelectIcebreakerQuestion
{
    /** @return array{icebreaker_question_id: string|null, question: string} */
    public function for(User $recipient): array
    {
        $handedOut = Meeting::query()
            ->where('recipient_id', $recipient->id)
            ->whereNotNull('icebreaker_question_id')
            ->pluck('icebreaker_question_id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();

        $question = IcebreakerQuestion::randomExcluding($handedOut)
            ?? IcebreakerQuestion::randomExcluding();

        if ($question !== null) {
            return [
                'icebreaker_question_id' => $question->id,
                'question' => $question->question,
            ];
        }

        return [
            'icebreaker_question_id' => null,
            'question' => Arr::random(config('meetme.fallback_questions')),
        ];
    }
}
