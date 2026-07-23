<?php

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\ConferenceRecap;
use Illuminate\Support\Facades\Notification;

it('sends a recap to users with at least one confirmed meeting', function () {
    Notification::fake();

    $connected = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $connected->id]);

    $this->artisan('meetme:send-recaps')->assertSuccessful();

    Notification::assertSentTo($connected, ConferenceRecap::class);
});

it('skips users with no confirmed meetings', function () {
    Notification::fake();

    $lonely = User::factory()->create();
    $pendingOnly = User::factory()->create();
    Meeting::factory()->create(['initiator_id' => $pendingOnly->id]); // pending, not confirmed

    $this->artisan('meetme:send-recaps')->assertSuccessful();

    Notification::assertNotSentTo($lonely, ConferenceRecap::class);
    Notification::assertNotSentTo($pendingOnly, ConferenceRecap::class);
});

it('counts a user once whether they were initiator or recipient', function () {
    Notification::fake();

    $initiator = User::factory()->create();
    $recipient = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $initiator->id, 'recipient_id' => $recipient->id]);

    $this->artisan('meetme:send-recaps')->assertSuccessful();

    Notification::assertSentTo($initiator, ConferenceRecap::class);
    Notification::assertSentTo($recipient, ConferenceRecap::class);
    Notification::assertCount(2);
});

it('renders without error and reflects redaction as of send time', function () {
    $user = User::factory()->create();
    Meeting::factory()->redacted()->create(['recipient_id' => $user->id]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'rating' => 5]);

    $mail = (new ConferenceRecap)->toMail($user);
    $rendered = (string) $mail->render();

    expect($rendered)->toContain('Answer redacted')
        ->and($rendered)->toContain('connection');
});

it('computes the average answer rating from meetings the user initiated', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'rating' => 4]);
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id, 'rating' => 5]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id, 'rating' => 1]); // as recipient, ignored

    expect($user->averageAnswerRating())->toBe(4.5);
});
