<?php

use App\Filament\Pages\ManageEvent;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->admin()->create()));

it('renders the event settings page for an admin', function () {
    $this->get(ManageEvent::getUrl())->assertSuccessful();
});

it('pre-fills the form from the current event', function () {
    Event::current()->update(['name' => 'Laracon US 2026']);

    Livewire::test(ManageEvent::class)
        ->assertSchemaStateSet(['name' => 'Laracon US 2026']);
});

it('persists edits to the single event record', function () {
    Livewire::test(ManageEvent::class)
        ->fillForm([
            'name' => 'DevConf 2026',
            'starts_at' => '2026-09-01 09:00:00',
            'ends_at' => '2026-09-02 17:00:00',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $event = Event::current();

    expect($event->name)->toBe('DevConf 2026')
        ->and($event->starts_at->toDateString())->toBe('2026-09-01')
        ->and($event->ends_at->toDateString())->toBe('2026-09-02')
        ->and(Event::query()->count())->toBe(1);
});

it('requires a conference name', function () {
    Livewire::test(ManageEvent::class)
        ->fillForm(['name' => ''])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('is not reachable by non-admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(ManageEvent::getUrl())
        ->assertForbidden();
});
