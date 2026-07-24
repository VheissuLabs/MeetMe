<?php

use App\Filament\Pages\ManageEvent;
use App\Filament\Widgets\LatestActivity;
use App\Models\Meeting;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->actingAs($this->admin = User::factory()->admin()->create()));

it('renders recent activity on the admin dashboard', function () {
    Meeting::factory()->create();

    Livewire::test(LatestActivity::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Activity::query()->latest()->get());
});

it('filters the feed by log type', function () {
    Meeting::factory()->create();

    Livewire::test(LatestActivity::class)
        ->filterTable('log_name', 'question')
        ->assertCanNotSeeTableRecords(Activity::query()->where('log_name', 'meeting')->get());
});

it('records admin panel actions with the acting admin as causer', function () {
    Livewire::test(ManageEvent::class)
        ->fillForm(['name' => 'DevConf 2026'])
        ->call('save')
        ->assertHasNoFormErrors();

    $activity = Activity::query()->where('log_name', 'admin')->latest('id')->first();

    expect($activity->description)->toBe('Saved event settings')
        ->and($activity->causer_id)->toBe($this->admin->id);
});
