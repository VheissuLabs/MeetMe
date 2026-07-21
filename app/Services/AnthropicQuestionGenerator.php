<?php

namespace App\Services;

use App\Ai\Agents\IcebreakerQuestionWriter;
use Laravel\Ai\Responses\StructuredAgentResponse;

class AnthropicQuestionGenerator implements QuestionGenerator
{
    public function __construct(private IcebreakerQuestionWriter $writer) {}

    /** @return list<string> */
    public function generate(int $count): array
    {
        $conference = config('meetme.conference_name');

        $response = $this->writer->prompt(
            "Write exactly {$count} icebreaker questions for attendees of {$conference}."
        );

        $structured = $response instanceof StructuredAgentResponse ? $response->toArray() : [];

        $questions = [];

        foreach ((array) ($structured['questions'] ?? []) as $question) {
            if (is_string($question) && filled($question) && ! in_array($question, $questions, true)) {
                $questions[] = $question;

                if (count($questions) === $count) {
                    break;
                }
            }
        }

        return $questions;
    }
}
