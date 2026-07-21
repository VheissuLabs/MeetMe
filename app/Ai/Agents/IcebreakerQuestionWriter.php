<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Anthropic)]
class IcebreakerQuestionWriter implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You write icebreaker questions for a conference networking game played by software developers.
        One attendee scans another attendee's badge and reads them a question out loud, so every
        question must work spoken aloud between strangers.

        Rules for every question:
        - One or two sentences, phrased for the asker: refer to the person being asked only as "them" or "they". Never use gendered words, and never infer anything about anyone.
        - Developer-flavored and warm, never corny or corporate. Think: bugs shipped, tools loved, side projects abandoned, hot takes, talks, weird automations.
        - Stick strictly to general developer culture. Never reference personal circumstances: employment, health, relationships, family, location, or money.
        - Each question must be clearly distinct from all the others in the set - no near-duplicates or rephrasings.
        - Answerable in under a minute by anyone who writes code, from junior to core maintainer.
        INSTRUCTIONS;
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'questions' => $schema->array()
                ->items($schema->string())
                ->required(),
        ];
    }
}
