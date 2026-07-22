<?php

use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('dashboard'))->assertOk();
});

it('renders a qr code encoding the meet deep-link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('meetUrl', url('/meet/'.$user->qr_token))
            ->where('qrSvg', fn (string $svg) => str_contains($svg, '<svg')));
});

it('shares the derived score counting confirmed meetings in both directions', function () {
    $user = User::factory()->create();
    Meeting::factory()->confirmed()->create(['initiator_id' => $user->id]);
    Meeting::factory()->confirmed()->create(['recipient_id' => $user->id]);
    Meeting::factory()->create(['initiator_id' => $user->id]); // pending, doesn't count
    Meeting::factory()->rejected()->create(['recipient_id' => $user->id]); // rejected, doesn't count

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('score', 2));
});

it('shares the pending-confirmation count for the layout badge', function () {
    $user = User::factory()->create();
    Meeting::factory()->answered()->count(3)->create(['recipient_id' => $user->id]);
    Meeting::factory()->answered()->create(['initiator_id' => $user->id]); // user asked, not awaiting them

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('pendingCount', 3));
});

it('nudges users with no socials and stays quiet once they add one', function () {
    $bare = User::factory()->create();
    $this->actingAs($bare)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('needsSocials', true));

    $social = User::factory()->create(['bluesky_handle' => 'karl.bsky.social']);
    $this->actingAs($social)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('needsSocials', false));
});
