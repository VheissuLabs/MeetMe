<?php

use App\Actions\GetLeaderboard;
use App\Enums\MeetingStatus;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Database\Seeders\MeetMeDemoSeeder;

it('produces a fully explorable data set without an AI key', function () {
    config(['ai.default' => null]); // prove no AI provider is touched

    $this->seed(MeetMeDemoSeeder::class);

    expect(User::query()->count())->toBe(30)
        ->and(IcebreakerQuestion::query()->count())->toBeGreaterThanOrEqual(50)
        ->and(Meeting::query()->where('status', MeetingStatus::Confirmed)->count())->toBeGreaterThan(0)
        ->and(Meeting::query()->where('status', MeetingStatus::Pending)->count())->toBeGreaterThan(0)
        ->and(Meeting::query()->where('status', MeetingStatus::Answered)->count())->toBeGreaterThan(0)
        ->and(Meeting::query()->where('status', MeetingStatus::Rejected)->count())->toBeGreaterThan(0)
        ->and(Meeting::query()->whereNotNull('answer_redacted_at')->count())->toBeGreaterThan(0);
});

it('creates a known explorable login', function () {
    $this->seed(MeetMeDemoSeeder::class);

    $explorer = User::query()->firstWhere('email', 'test@example.com');

    expect($explorer)->not->toBeNull()
        ->and($explorer->qr_token)->not->toBeNull()
        ->and($explorer->githubAccount)->not->toBeNull();
});

it('produces a believable leaderboard spread', function () {
    $this->seed(MeetMeDemoSeeder::class);

    $rankings = app(GetLeaderboard::class)->get();

    expect(count($rankings))->toBeGreaterThan(5)
        ->and($rankings[0]['score'])->toBeGreaterThan(1);
});
