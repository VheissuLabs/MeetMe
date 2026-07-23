<?php

use App\Actions\GetLeaderboard;
use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

/** @return array<int, array{name: string, pronouns: string|null, avatar_url: string|null, score: int}> */
function leaderboard(): array
{
    return app(GetLeaderboard::class)->get();
}

it('loads without authentication', function () {
    $this->get(route('leaderboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Leaderboard'));
});

it('counts confirmed meetings in both directions', function () {
    $alice = User::factory()->create(['name' => 'Alice']);
    $bob = User::factory()->create(['name' => 'Bob']);
    $carol = User::factory()->create(['name' => 'Carol']);

    // Alice: 2 confirmed (one as initiator, one as recipient)
    Meeting::factory()->confirmed()->create(['initiator_id' => $alice->id, 'recipient_id' => $bob->id]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $carol->id, 'recipient_id' => $alice->id]);
    // Bob: 1 (the one with Alice). Carol: 1 (the one with Alice).

    $rankings = collect(leaderboard());

    expect($rankings->firstWhere('name', 'Alice')['score'])->toBe(2)
        ->and($rankings->firstWhere('name', 'Bob')['score'])->toBe(1)
        ->and($rankings->firstWhere('name', 'Carol')['score'])->toBe(1)
        ->and($rankings->first()['name'])->toBe('Alice');
});

it('ignores non-confirmed meetings', function () {
    $user = User::factory()->create();
    Meeting::factory()->create(['initiator_id' => $user->id]); // pending
    Meeting::factory()->answered()->create(['recipient_id' => $user->id]); // answered
    Meeting::factory()->rejected()->create(['initiator_id' => $user->id]); // rejected

    expect(leaderboard())->toBeEmpty();
});

it('omits users with no confirmed meetings', function () {
    User::factory()->count(3)->create(); // nobody has met anyone

    expect(leaderboard())->toBeEmpty();
});

it('breaks ties by earliest last-confirmation', function () {
    $early = User::factory()->create(['name' => 'Early']);
    $late = User::factory()->create(['name' => 'Late']);
    $partnerA = User::factory()->create();
    $partnerB = User::factory()->create();

    // Both reach a score of 1, but Early confirmed earlier.
    Meeting::factory()->confirmed()->create([
        'initiator_id' => $early->id, 'recipient_id' => $partnerA->id, 'resolved_at' => now()->subHour(),
    ]);
    Meeting::factory()->confirmed()->create([
        'initiator_id' => $late->id, 'recipient_id' => $partnerB->id, 'resolved_at' => now(),
    ]);

    $names = collect(leaderboard())->pluck('name');

    expect($names->search('Early'))->toBeLessThan($names->search('Late'));
});

it('never lets ratings influence the ranking', function () {
    $lowRated = User::factory()->create(['name' => 'LowRated']);
    $highRated = User::factory()->create(['name' => 'HighRated']);

    // LowRated has 2 confirmed (rating 1); HighRated has 1 confirmed (rating 5).
    Meeting::factory()->confirmed()->create(['initiator_id' => $lowRated->id, 'rating' => 1]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $lowRated->id, 'rating' => 1]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $highRated->id, 'rating' => 5]);

    expect(collect(leaderboard())->first()['name'])->toBe('LowRated');
});
