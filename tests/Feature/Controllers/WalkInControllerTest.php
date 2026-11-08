<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('shows the walk-in form to a guard', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('walk-ins.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('walk-in/Create'));
});

it('lets a guard register a walk-in visitor', function (): void {
    $guard = User::factory()->guard()->create();

    $response = $this->actingAs($guard)->post(route('walk-ins.store'), [
        'visitor_name' => 'Pedro Reyes',
        'visitor_contact' => '09181110001',
        'purpose' => 'Delivery of school supplies',
        'host_name' => 'Supply Office',
        'expected_time_out' => '16:00',
        'expected_arrival' => '10:15',
    ]);

    $visit = Visit::query()->first();

    expect($visit)->not->toBeNull();

    $response->assertRedirectToRoute('visits.show', $visit);

    expect($visit->status)->toBe(VisitStatus::Pending)
        ->and($visit->is_walk_in)->toBeTrue()
        ->and($visit->user_id)->toBeNull()
        ->and($visit->visitor_name)->toBe('Pedro Reyes')
        ->and($visit->expected_time_out)->toStartWith('16:00')
        ->and($visit->visit_date->toDateString())->toBe(now()->toDateString());
});

it('requires visitor details for a walk-in', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->post(route('walk-ins.store'), [])
        ->assertSessionHasErrors(['visitor_name', 'visitor_contact', 'purpose', 'host_name']);

    expect(Visit::query()->count())->toBe(0);
});

it('blocks visitors from registering walk-ins', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->get(route('walk-ins.create'))
        ->assertNotFound();

    $this->actingAs($visitor)
        ->post(route('walk-ins.store'), [
            'visitor_name' => 'Someone',
            'visitor_contact' => '09181110001',
            'purpose' => 'Test',
            'host_name' => 'Office',
            'expected_time_out' => '16:00',
        ])
        ->assertNotFound();
});
