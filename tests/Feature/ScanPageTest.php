<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the scanner for an authenticated user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('scan'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Scan'));
});

it('redirects guests to login', function () {
    $this->get(route('scan'))->assertRedirect(route('login'));
});
