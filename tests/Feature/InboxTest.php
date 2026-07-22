<?php

use App\Models\Meeting;
use App\Models\User;

it('returns only answered meetings where the user is the recipient', function () {
    $user = User::factory()->create();

    $awaiting = Meeting::factory()->answered()->create(['recipient_id' => $user->id]);

    // Noise that must never appear in the inbox:
    Meeting::factory()->answered()->create(['initiator_id' => $user->id]); // user is the asker
    Meeting::factory()->create(['recipient_id' => $user->id]); // still pending
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]); // already resolved
    Meeting::factory()->answered()->create(); // someone else's meeting

    $response = $this->actingAs($user)->getJson(route('inbox'));

    $response->assertOk()->assertJsonCount(1, 'meetings');

    expect($response->json('meetings.0.id'))->toBe($awaiting->id)
        ->and($response->json('meetings.0.question'))->toBe($awaiting->question)
        ->and($response->json('meetings.0.initiator.name'))->toBe($awaiting->initiator->name);
});

it('orders the inbox by most recently answered first', function () {
    $user = User::factory()->create();
    $older = Meeting::factory()->answered()->create(['recipient_id' => $user->id, 'answered_at' => now()->subHour()]);
    $newer = Meeting::factory()->answered()->create(['recipient_id' => $user->id, 'answered_at' => now()]);

    $ids = $this->actingAs($user)->getJson(route('inbox'))->json('meetings.*.id');

    expect($ids)->toBe([$newer->id, $older->id]);
});

it('returns an empty list when nothing awaits confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson(route('inbox'))
        ->assertOk()
        ->assertExactJson(['meetings' => []]);
});

it('requires authentication', function () {
    $this->getJson(route('inbox'))->assertUnauthorized();
});
