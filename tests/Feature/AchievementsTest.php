<?php

use App\Actions\AwardAchievements;
use App\Enums\Achievement;
use App\Enums\MeetingStatus;
use App\Events\AchievementUnlocked;
use App\Models\Event as EventModel;
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
    // Midday so the time-of-day badges (night owl / early bird) don't also fire.
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id, 'resolved_at' => now()->setTime(14, 0)]);

    award($user);
    Event::assertDispatched(AchievementUnlocked::class, 1);

    award($user); // already earned — nothing new
    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('awards early-tester to users who joined before the event started', function () {
    config(['meetme.starts_at' => now()->addWeek()->toDateString()]);
    EventModel::current(); // seed starts_at from config
    $user = User::factory()->create();

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::EarlyTester);
});

it('does not award early-tester without an event start date', function () {
    $user = User::factory()->create();

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->not->toContain(Achievement::EarlyTester);
});

it('does not award early-tester to users who joined after the event started', function () {
    config(['meetme.starts_at' => now()->subWeek()->toDateString()]);
    EventModel::current();
    $user = User::factory()->create();

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->not->toContain(Achievement::EarlyTester);
});

it('backfills achievements for existing users via the command', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]);

    $this->artisan('meetme:backfill-achievements')->assertSuccessful();

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection);
});

it('grants an achievement outright and is idempotent', function () {
    $user = User::factory()->create();
    $action = app(AwardAchievements::class);

    expect($action->grant($user, Achievement::EarlyTester))->toBeTrue()
        ->and($action->grant($user, Achievement::EarlyTester))->toBeFalse()
        ->and($user->achievements()->where('achievement', Achievement::EarlyTester)->count())->toBe(1);
});

it('awards curious after scanning ten people', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(10)->create(['initiator_id' => $user->id]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::Curious);
});

it('awards crowd-pleaser for a high average across enough ratings', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(5)->create(['initiator_id' => $user->id, 'rating' => 5]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::CrowdPleaser);
});

it('does not award crowd-pleaser without enough ratings', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(3)->create(['initiator_id' => $user->id, 'rating' => 5]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->not->toContain(Achievement::CrowdPleaser);
});

it('awards all-wired-up when every social is linked', function () {
    $user = User::factory()->withGithub()->create([
        'x_username' => 'karl',
        'bluesky_handle' => 'karl.bsky.social',
        'linkedin_username' => 'karl-murray',
    ]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::AllWiredUp);
});

it('does not award all-wired-up with a social missing', function () {
    $user = User::factory()->withGithub()->create([
        'x_username' => 'karl',
        'bluesky_handle' => 'karl.bsky.social',
        'linkedin_username' => null,
    ]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->not->toContain(Achievement::AllWiredUp);
});

it('awards popular after being scanned by ten people', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(10)->create(['recipient_id' => $user->id]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::Popular);
});

it('awards prolific after answering twenty-five icebreakers', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->count(25)->create(['recipient_id' => $user->id]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::Prolific);
});

it('awards night owl for a late confirmed meeting', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create([
        'recipient_id' => $user->id,
        'resolved_at' => now()->setTime(22, 30),
    ]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::NightOwl);
});

it('awards early bird for an early confirmed meeting', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create([
        'recipient_id' => $user->id,
        'resolved_at' => now()->setTime(7, 15),
    ]);

    award($user);

    expect($user->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::EarlyBird);
});

it('awards completionist only after every other badge is earned', function () {
    $user = User::factory()->create();
    $others = collect(Achievement::cases())->reject(fn (Achievement $a) => $a === Achievement::Completionist);

    // Grant all but one other badge — completionist should not unlock.
    $others->skip(1)->each(fn (Achievement $a) => $user->achievements()->create(['achievement' => $a, 'earned_at' => now()]));
    award($user);
    expect($user->achievements()->where('achievement', Achievement::Completionist->value)->exists())->toBeFalse();

    // Grant the final one — now it unlocks.
    $user->achievements()->create(['achievement' => $others->first(), 'earned_at' => now()]);
    award($user);
    expect($user->achievements()->where('achievement', Achievement::Completionist->value)->exists())->toBeTrue();
});

it('awards both participants when a meeting is confirmed', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $meeting = Meeting::factory()->answered()->create(['initiator_id' => $a->id, 'recipient_id' => $b->id]);

    $meeting->update(['status' => MeetingStatus::Confirmed]);

    expect($a->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection)
        ->and($b->achievements()->get()->pluck('achievement')->all())->toContain(Achievement::FirstConnection);
});
