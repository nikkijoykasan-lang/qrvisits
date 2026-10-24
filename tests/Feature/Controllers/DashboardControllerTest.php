<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Visit;

it('shows the visitor dashboard to visitors', function (): void {
    $visitor = User::factory()->create();
    Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard/Visitor')
            ->where('stats.pending', 1));
});

it('shows the guard dashboard to guards', function (): void {
    $guard = User::factory()->guard()->create();
    Visit::factory()->inside()->create();

    $this->actingAs($guard)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard/Guard')
            ->where('stats.inside', 1)
            ->where('canApprove', false));
});

it('shows the admin dashboard to admins', function (): void {
    $admin = User::factory()->admin()->create();
    Visit::factory()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard/Admin')
            ->where('canApprove', true));
});

it('shows the live board to staff with everyone inside', function (): void {
    $guard = User::factory()->guard()->create();
    Visit::factory()->inside()->create();
    Visit::factory()->approved()->create();

    $this->actingAs($guard)
        ->get(route('gate.board'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('gate/Board')
            ->has('visits', 1)
            ->has('expected', 1)
            ->where('stats.inside', 1));
});

it('hides the live board from visitors', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->get(route('gate.board'))
        ->assertNotFound();
});

it('prints the emergency list for staff', function (): void {
    $guard = User::factory()->guard()->create();
    Visit::factory()->inside()->create();

    $this->actingAs($guard)
        ->get(route('emergency-list'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('emergency-list/Index')
            ->has('visits', 1));
});

it('opens the gate scan page for guards', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('gate.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('gate/Scan'));
});

it('blocks visitors from the gate scan page', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->get(route('gate.index'))
        ->assertNotFound();
});
