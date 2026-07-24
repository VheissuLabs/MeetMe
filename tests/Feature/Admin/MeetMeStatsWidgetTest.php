<?php

use App\Filament\Widgets\MeetMeStatsWidget;
use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** @return array<int, Stat> */
function stats(): array
{
    $widget = new MeetMeStatsWidget;

    return (fn () => $this->getStats())->call($widget);
}

function statValue(string $label): mixed
{
    return collect(stats())->first(fn ($stat) => $stat->getLabel() === $label)?->getValue();
}

it('counts attendees, connections, in-flight, and rejected', function () {
    // Each meeting factory makes its own two users, so pairs never collide.
    Meeting::factory()->confirmed()->count(2)->create();
    Meeting::factory()->create(); // pending
    Meeting::factory()->answered()->create();
    Meeting::factory()->rejected()->create();

    expect(statValue('Attendees'))->toBe(User::query()->count())
        ->and(statValue('Connections'))->toBe(2)
        ->and(statValue('In flight'))->toBe(2) // 1 pending + 1 answered
        ->and(statValue('Rejected'))->toBe(1);
});

it('renders on the admin dashboard for an admin', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire\Livewire::test(MeetMeStatsWidget::class)
        ->assertOk()
        ->assertSee('Connections');
});
