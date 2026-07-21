<?php

namespace App\Console\Commands;

use App\Actions\GenerateGlobalQuestions;
use App\Models\IcebreakerQuestion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('meetme:generate-questions {--fresh : Wipe the pool and regenerate from scratch}')]
#[Description('Generate the global AI icebreaker question pool')]
class GenerateQuestionsCommand extends Command
{
    public function handle(GenerateGlobalQuestions $generate): int
    {
        $created = $generate->handle(fresh: (bool) $this->option('fresh'));

        $total = IcebreakerQuestion::query()->count();

        $this->info($created > 0
            ? "Generated {$created} questions ({$total} total in the pool)."
            : "Pool already full ({$total} questions) — nothing to generate. Use --fresh to regenerate.");

        return self::SUCCESS;
    }
}
