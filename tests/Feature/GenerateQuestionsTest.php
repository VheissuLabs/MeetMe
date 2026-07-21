<?php

use App\Ai\Agents\IcebreakerQuestionWriter;
use App\Models\IcebreakerQuestion;
use App\Services\AnthropicQuestionGenerator;
use App\Services\QuestionGenerator;
use Laravel\Ai\Prompts\AgentPrompt;

function bindStubGenerator(): void
{
    app()->bind(QuestionGenerator::class, fn () => new class implements QuestionGenerator
    {
        public function generate(int $count): array
        {
            return collect(range(1, $count))
                ->map(fn (int $i) => "Stub question {$i}?")
                ->all();
        }
    });
}

it('fills an empty pool to the configured count', function () {
    bindStubGenerator();
    config(['meetme.question_count' => 50]);

    $this->artisan('meetme:generate-questions')
        ->expectsOutputToContain('Generated 50 questions (50 total in the pool).')
        ->assertSuccessful();

    expect(IcebreakerQuestion::query()->count())->toBe(50);
});

it('tops up a partial pool instead of duplicating it', function () {
    bindStubGenerator();
    config(['meetme.question_count' => 50]);
    IcebreakerQuestion::factory()->count(20)->create();

    $this->artisan('meetme:generate-questions')->assertSuccessful();

    expect(IcebreakerQuestion::query()->count())->toBe(50);
});

it('does nothing when the pool is already full', function () {
    bindStubGenerator();
    config(['meetme.question_count' => 10]);
    IcebreakerQuestion::factory()->count(10)->create();

    $this->artisan('meetme:generate-questions')
        ->expectsOutputToContain('Pool already full')
        ->assertSuccessful();

    expect(IcebreakerQuestion::query()->count())->toBe(10);
});

it('wipes and regenerates with --fresh', function () {
    bindStubGenerator();
    config(['meetme.question_count' => 10]);
    $old = IcebreakerQuestion::factory()->create(['question' => 'Old question?']);

    $this->artisan('meetme:generate-questions', ['--fresh' => true])->assertSuccessful();

    expect(IcebreakerQuestion::query()->count())->toBe(10)
        ->and(IcebreakerQuestion::query()->whereKey($old->id)->exists())->toBeFalse();
});

it('prompts anthropic with the conference name and zero user data', function () {
    config(['meetme.conference_name' => 'Laracon US 2026']);
    IcebreakerQuestionWriter::fake();

    app(AnthropicQuestionGenerator::class)->generate(5);

    IcebreakerQuestionWriter::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Laracon US 2026')
        && $prompt->contains('exactly 5'));
});

it('deduplicates and trims the model response', function () {
    IcebreakerQuestionWriter::fake(fn () => ['questions' => ['Same?', 'Same?', 'Other?', '', 42, 'Third?', 'Fourth?']]);

    $questions = app(AnthropicQuestionGenerator::class)->generate(3);

    expect($questions)->toBe(['Same?', 'Other?', 'Third?']);
});
