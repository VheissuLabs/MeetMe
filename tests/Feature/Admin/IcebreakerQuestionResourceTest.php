<?php

use App\Filament\Resources\IcebreakerQuestions\IcebreakerQuestionResource;
use App\Filament\Resources\IcebreakerQuestions\Pages\CreateIcebreakerQuestion;
use App\Filament\Resources\IcebreakerQuestions\Pages\EditIcebreakerQuestion;
use App\Filament\Resources\IcebreakerQuestions\Pages\ListIcebreakerQuestions;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->admin()->create()));

it('lists the question pool for an admin', function () {
    $question = IcebreakerQuestion::factory()->create();

    Livewire::test(ListIcebreakerQuestions::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$question]);
});

it('searches questions by text', function () {
    $cursed = IcebreakerQuestion::factory()->create(['question' => 'Cursed production bug?']);
    $tabs = IcebreakerQuestion::factory()->create(['question' => 'Tabs or spaces?']);

    Livewire::test(ListIcebreakerQuestions::class)
        ->searchTable('Cursed')
        ->assertCanSeeTableRecords([$cursed])
        ->assertCanNotSeeTableRecords([$tabs]);
});

it('counts how many meetings used a question', function () {
    $question = IcebreakerQuestion::factory()->create();
    Meeting::factory()->count(2)->create(['icebreaker_question_id' => $question->id]);

    Livewire::test(ListIcebreakerQuestions::class)
        ->assertTableColumnExists('meetings_count');

    expect(IcebreakerQuestion::withCount('meetings')->find($question->id)->meetings_count)->toBe(2);
});

it('creates a hand-written question', function () {
    Livewire::test(CreateIcebreakerQuestion::class)
        ->fillForm(['question' => 'What side project are you neglecting?'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(IcebreakerQuestion::where('question', 'What side project are you neglecting?')->exists())->toBeTrue();
});

it('edits a duds wording', function () {
    $question = IcebreakerQuestion::factory()->create(['question' => 'Flat joke?']);

    Livewire::test(EditIcebreakerQuestion::class, ['record' => $question->getKey()])
        ->fillForm(['question' => 'A better, conversation-starting question?'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($question->refresh()->question)->toBe('A better, conversation-starting question?');
});

it('deletes a question without touching past meetings', function () {
    $question = IcebreakerQuestion::factory()->create();
    $meeting = Meeting::factory()->create([
        'icebreaker_question_id' => $question->id,
        'question' => 'Snapshotted text',
    ]);

    Livewire::test(EditIcebreakerQuestion::class, ['record' => $question->getKey()])
        ->callAction('delete');

    expect(IcebreakerQuestion::find($question->id))->toBeNull()
        ->and($meeting->refresh()->icebreaker_question_id)->toBeNull()
        ->and($meeting->question)->toBe('Snapshotted text');
});

it('requires validation on create', function () {
    Livewire::test(CreateIcebreakerQuestion::class)
        ->fillForm(['question' => ''])
        ->call('create')
        ->assertHasFormErrors(['question' => 'required']);
});

it('forbids non-admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(IcebreakerQuestionResource::getUrl('index'))
        ->assertForbidden();
});
