<?php

use App\Models\IcebreakerQuestion;
use Illuminate\Support\Str;

it('lives in a global pool with ulid keys', function () {
    $question = IcebreakerQuestion::factory()->create();

    expect(Str::isUlid($question->id))->toBeTrue()
        ->and($question->question)->not->toBeEmpty();
});

it('selects a random question excluding already-used ids', function () {
    $used = IcebreakerQuestion::factory()->count(2)->create();
    $fresh = IcebreakerQuestion::factory()->create();

    $picked = IcebreakerQuestion::randomExcluding($used->pluck('id')->all());

    expect($picked?->id)->toBe($fresh->id);
});

it('returns null when every question is excluded or the pool is empty', function () {
    expect(IcebreakerQuestion::randomExcluding())->toBeNull();

    $only = IcebreakerQuestion::factory()->create();

    expect(IcebreakerQuestion::randomExcluding([$only->id]))->toBeNull();
});

it('ships a non-empty gender-neutral fallback pool', function () {
    $questions = config('meetme.fallback_questions');

    expect($questions)->toBeArray()
        ->and(count($questions))->toBeGreaterThanOrEqual(10)
        ->and($questions)->each(fn ($question) => $question
        ->toBeString()
        ->not->toContain(' he ', ' she ', ' his ', ' her '));
});
