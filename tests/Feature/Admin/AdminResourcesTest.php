<?php

use App\Enums\MeetingStatus;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Resources\Meetings\Pages\ListMeetings;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->admin()->create()));

it('lists users for an admin', function () {
    $alice = User::factory()->create(['name' => 'Alice Zephyr']);

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$alice]);
});

it('searches users by name', function () {
    $alice = User::factory()->create(['name' => 'Alice Zephyr']);
    $bob = User::factory()->create(['name' => 'Bob Quill']);

    Livewire::test(ListUsers::class)
        ->searchTable('Zephyr')
        ->assertCanSeeTableRecords([$alice])
        ->assertCanNotSeeTableRecords([$bob]);
});

it('exposes the inline admin toggle column', function () {
    User::factory()->create();

    Livewire::test(ListUsers::class)
        ->assertTableColumnExists('is_admin')
        ->assertTableColumnExists('email_visible');
});

it('cannot create users from the panel', function () {
    expect(UserResource::canCreate())->toBeFalse();
});

it('shows the attendee count as a navigation badge', function () {
    User::factory()->count(2)->create();

    // The acting admin from beforeEach plus the two created here.
    expect(UserResource::getNavigationBadge())->toBe((string) User::count());
});

it('lists meetings and filters by status', function () {
    $confirmed = Meeting::factory()->confirmed()->create();
    $pending = Meeting::factory()->create();

    Livewire::test(ListMeetings::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$confirmed, $pending])
        ->filterTable('status', MeetingStatus::Confirmed->value)
        ->assertCanSeeTableRecords([$confirmed])
        ->assertCanNotSeeTableRecords([$pending]);
});

it('cannot create meetings from the panel', function () {
    expect(MeetingResource::canCreate())->toBeFalse();
});

it('shows meeting times in 12-hour format', function () {
    $meeting = Meeting::factory()->confirmed()->create();
    $meeting->forceFill(['created_at' => Carbon::parse('2026-07-24 14:30:00')])->save();

    Livewire::test(ListMeetings::class)
        ->assertTableColumnFormattedStateSet('created_at', 'Jul 24, 2026 2:30 PM', $meeting);
});

it('forbids non-admins from the resources', function () {
    $this->actingAs(User::factory()->create())
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();
});
