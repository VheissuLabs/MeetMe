<?php

use App\Enums\MeetingStatus;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

it('creates a pending meeting from a scanned qr token', function () {
    IcebreakerQuestion::factory()->count(3)->create();
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($initiator)
        ->post(route('meetings.store'), ['qr_token' => $recipient->qr_token]);

    $meeting = Meeting::query()->sole();

    $response->assertRedirect(route('meetings.show', $meeting, absolute: false));

    expect($meeting->status)->toBe(MeetingStatus::Pending)
        ->and($meeting->initiator_id)->toBe($initiator->id)
        ->and($meeting->recipient_id)->toBe($recipient->id)
        ->and($meeting->question)->not->toBeEmpty()
        ->and($meeting->icebreaker_question_id)->not->toBeNull()
        ->and($meeting->pair_key)->toBe(Meeting::pairKeyFor($initiator, $recipient));
});

it('rejects scanning your own code', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('meetings.store'), ['qr_token' => $user->qr_token])
        ->assertSessionHasErrors('qr_token');

    expect(Meeting::query()->count())->toBe(0);
});

it('rejects tokens that resolve to nobody', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('meetings.store'), ['qr_token' => (string) Str::ulid()])
        ->assertSessionHasErrors('qr_token');
});

it('redirects duplicate scans to the existing meeting in both directions', function () {
    $one = User::factory()->create();
    $two = User::factory()->create();
    $meeting = Meeting::factory()->create(['initiator_id' => $one->id, 'recipient_id' => $two->id]);

    $this->actingAs($one)
        ->post(route('meetings.store'), ['qr_token' => $two->qr_token])
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    $this->actingAs($two)
        ->post(route('meetings.store'), ['qr_token' => $one->qr_token])
        ->assertRedirect(route('meetings.show', $meeting, absolute: false));

    expect(Meeting::query()->count())->toBe(1);
});

it('falls back to a config question when the pool is empty', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();

    $this->actingAs($initiator)
        ->post(route('meetings.store'), ['qr_token' => $recipient->qr_token]);

    $meeting = Meeting::query()->sole();

    expect($meeting->icebreaker_question_id)->toBeNull()
        ->and(config('meetme.fallback_questions'))->toContain($meeting->question);
});

it('never hands the same pool question to one recipient twice', function () {
    $questions = IcebreakerQuestion::factory()->count(2)->create();
    $recipient = User::factory()->create();

    Meeting::factory()->create([
        'recipient_id' => $recipient->id,
        'icebreaker_question_id' => $questions[0]->id,
        'question' => $questions[0]->question,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('meetings.store'), ['qr_token' => $recipient->qr_token]);

    $latest = Meeting::query()->latest('id')->first();

    expect($latest->icebreaker_question_id)->toBe($questions[1]->id);
});

it('allows repeats once a recipient has exhausted the pool', function () {
    $question = IcebreakerQuestion::factory()->create();
    $recipient = User::factory()->create();

    Meeting::factory()->create([
        'recipient_id' => $recipient->id,
        'icebreaker_question_id' => $question->id,
        'question' => $question->question,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('meetings.store'), ['qr_token' => $recipient->qr_token]);

    $latest = Meeting::query()->latest('id')->first();

    expect($latest->icebreaker_question_id)->toBe($question->id);
});

it('throttles scan spam per user', function () {
    config(['meetme.scan_rate_limit' => 2]);
    $initiator = User::factory()->create();
    $targets = User::factory()->count(3)->create();

    $this->actingAs($initiator);

    $this->post(route('meetings.store'), ['qr_token' => $targets[0]->qr_token]);
    $this->post(route('meetings.store'), ['qr_token' => $targets[1]->qr_token]);

    $this->post(route('meetings.store'), ['qr_token' => $targets[2]->qr_token])
        ->assertTooManyRequests();
});

it('requires authentication', function () {
    $recipient = User::factory()->create();

    $this->post(route('meetings.store'), ['qr_token' => $recipient->qr_token])
        ->assertRedirect(route('login', absolute: false));
});

it('lets both participants view the meeting and nobody else', function () {
    $meeting = Meeting::factory()->create();

    $this->actingAs($meeting->initiator)->get(route('meetings.show', $meeting))->assertOk();
    $this->actingAs($meeting->recipient)->get(route('meetings.show', $meeting))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('meetings.show', $meeting))->assertForbidden();
});

it('enforces pair uniqueness at the database level', function () {
    $one = User::factory()->create();
    $two = User::factory()->create();

    Meeting::factory()->create(['initiator_id' => $one->id, 'recipient_id' => $two->id]);
    Meeting::factory()->create(['initiator_id' => $two->id, 'recipient_id' => $one->id]);
})->throws(UniqueConstraintViolationException::class);
