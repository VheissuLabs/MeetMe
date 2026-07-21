<?php

namespace App\Actions;

use App\Models\IcebreakerQuestion;
use App\Services\QuestionGenerator;

class GenerateGlobalQuestions
{
    public function __construct(private QuestionGenerator $generator) {}

    public function handle(bool $fresh = false): int
    {
        if ($fresh) {
            IcebreakerQuestion::query()->delete();
        }

        $target = (int) config('meetme.question_count');
        $missing = $target - IcebreakerQuestion::query()->count();

        if ($missing < 1) {
            return 0;
        }

        $questions = $this->generator->generate($missing);

        collect($questions)->each(
            fn (string $question) => IcebreakerQuestion::create(['question' => $question])
        );

        return count($questions);
    }
}
