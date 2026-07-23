<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the landing page for guests with the configured conference name', function () {
    config(['meetme.conference_name' => 'Laracon US 2026']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Landing')
            ->where('conferenceName', 'Laracon US 2026'));
});

it('redirects authenticated users to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

it('ships no authenticated app data in the landing props', function () {
    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('conferenceName')
            ->missing('connections')
            ->missing('rankings'));
});
