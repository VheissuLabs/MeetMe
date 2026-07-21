<?php

use App\Models\IcebreakerQuestion;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

it('belongs to the user the question is about', function () {
    $question = IcebreakerQuestion::factory()->create();

    expect($question->user)->toBeInstanceOf(User::class)
        ->and(Str::isUlid($question->id))->toBeTrue();
});

it('scopes unconsumed questions', function () {
    $user = User::factory()->create();
    IcebreakerQuestion::factory()->count(3)->for($user)->create();
    IcebreakerQuestion::factory()->count(2)->for($user)->consumed()->create();

    expect(IcebreakerQuestion::query()->for($user)->unconsumed()->count())->toBe(3);
});

it('selects a random unconsumed question for the right user only', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    IcebreakerQuestion::factory()->count(2)->for($stranger)->create();
    $only = IcebreakerQuestion::factory()->for($user)->create();
    IcebreakerQuestion::factory()->for($user)->consumed()->create();

    expect(IcebreakerQuestion::randomUnconsumedFor($user)?->id)->toBe($only->id);
});

it('returns null when the pool is empty', function () {
    $user = User::factory()->create();

    expect(IcebreakerQuestion::randomUnconsumedFor($user))->toBeNull();
});

it('rejects consuming one pool question for two meetings', function () {
    $meetingId = (string) Str::ulid();
    IcebreakerQuestion::factory()->create(['meeting_id' => $meetingId]);
    IcebreakerQuestion::factory()->create(['meeting_id' => $meetingId]);
})->throws(UniqueConstraintViolationException::class);

it('ships a non-empty gender-neutral fallback pool', function () {
    $questions = config('meetme.fallback_questions');

    expect($questions)->toBeArray()
        ->and(count($questions))->toBeGreaterThanOrEqual(10)
        ->and($questions)->each(fn ($question) => $question
        ->toBeString()
        ->not->toContain(' he ', ' she ', ' his ', ' her '));
});
