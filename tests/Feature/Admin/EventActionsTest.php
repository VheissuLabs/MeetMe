<?php

use App\Filament\Pages\ManageEvent;
use App\Models\Event;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\ConferenceRecap;
use App\Services\QuestionGenerator;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->admin()->create()));

function bindStubQuestionGenerator(): void
{
    app()->bind(QuestionGenerator::class, fn () => new class implements QuestionGenerator
    {
        public function generate(int $count): array
        {
            return collect(range(1, $count))->map(fn (int $i) => "Stub question {$i}?")->all();
        }
    });
}

it('generates the question pool from a header action', function () {
    bindStubQuestionGenerator();
    config(['meetme.question_count' => 10]);

    Livewire::test(ManageEvent::class)
        ->callAction('generateQuestions', data: ['fresh' => false])
        ->assertHasNoActionErrors();

    expect(IcebreakerQuestion::query()->count())->toBe(10);
});

it('wipes and regenerates when fresh is toggled', function () {
    bindStubQuestionGenerator();
    config(['meetme.question_count' => 5]);
    IcebreakerQuestion::factory()->count(3)->create();

    Livewire::test(ManageEvent::class)
        ->callAction('generateQuestions', data: ['fresh' => true]);

    expect(IcebreakerQuestion::query()->count())->toBe(5);
});

it('sends recap emails from a header action', function () {
    Notification::fake();
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id]);

    Livewire::test(ManageEvent::class)
        ->callAction('sendRecaps')
        ->assertHasNoActionErrors();

    Notification::assertSentTo($user, ConferenceRecap::class);
});

it('runs the answer purge from a header action', function () {
    config(['meetme.purge_answers_after_days' => 30]);
    Event::current()->update(['ends_at' => now()->subDays(40)]);
    $meeting = Meeting::factory()->confirmed()->create();

    Livewire::test(ManageEvent::class)
        ->callAction('purgeAnswers')
        ->assertHasNoActionErrors();

    expect($meeting->refresh()->answer)->toBeNull();
});

it('does not expose the actions to non-admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(ManageEvent::getUrl())
        ->assertForbidden();
});
