<?php

use App\Actions\AwardAchievements;
use App\Enums\Achievement;
use App\Enums\MeetingStatus;
use App\Events\AchievementUnlocked;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function award(User $user): void
{
    app(AwardAchievements::class)->for($user);
}

it('awards the first-connection badge at one connection', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection);
});

it('awards every milestone the score has passed', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(3)->create(['recipient_id' => $user->id]);

    award($user);

    $earned = $user->achievements()->get()->pluck('achievement')->all();

    expect($earned)->toContain(Achievement::FirstConnection)
        ->and($earned)->toContain(Achievement::GettingStarted)
        ->and($earned)->not->toContain(Achievement::SocialButterfly);
});

it('awards five-star for a top-rated icebreaker answer', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'rating' => 5]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FiveStar);
});

it('is idempotent and never duplicates a badge', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]);

    award($user);
    award($user);

    expect($user->achievements()->where('achievement', Achievement::FirstConnection->value)->count())->toBe(1);
});

it('broadcasts only newly unlocked achievements', function () {
    Event::fake([AchievementUnlocked::class]);
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]);

    award($user);
    Event::assertDispatched(AchievementUnlocked::class, 1);

    award($user); // already earned — nothing new
    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('awards both participants when a meeting is confirmed', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $meeting = Meeting::factory()->answered()->create(['initiator_id' => $a->id, 'recipient_id' => $b->id]);

    $meeting->update(['status' => MeetingStatus::Confirmed]);

    expect($a->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection)
        ->and($b->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection);
});
