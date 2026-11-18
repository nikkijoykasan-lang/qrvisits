<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('shows the walk-in form to a visitor', function (): void {
    $visitor = User::factory()->create(['contact_number' => '09171234567']);

    $this->actingAs($visitor)
        ->get(route('walk-ins.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('walk-in/Create')
            ->where('visitor.name', $visitor->name)
            ->where('visitor.contact', '09171234567'));
});

it('lets a visitor register themselves as a walk-in and issues a pass', function (): void {
    $visitor = User::factory()->create(['name' => 'Maria Santos']);

    $response = $this->actingAs($visitor)->post(route('walk-ins.store'), [
        'visitor_name' => 'Someone Else',
        'visitor_contact' => '09181110001',
        'purpose' => 'Delivery of school supplies',
        'host_name' => 'Supply Office',
        'expected_time_out' => '16:00',
    ]);

    $visit = Visit::query()->first();

    expect($visit)->not->toBeNull();

    $response->assertRedirectToRoute('visits.show', $visit);

    expect($visit->status)->toBe(VisitStatus::Approved)
        ->and($visit->is_walk_in)->toBeTrue()
        ->and($visit->user_id)->toBe($visitor->id)
        ->and($visit->visitor_name)->toBe('Maria Santos')
        ->and($visit->pass_code)->not->toBeNull()
        ->and($visit->expected_time_out)->toStartWith('16:00')
        ->and($visit->visit_date->toDateString())->toBe(now()->toDateString());
});

it('requires the walk-in details', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->post(route('walk-ins.store'), [])
        ->assertSessionHasErrors([
            'visitor_contact',
            'purpose',
            'host_name',
            'expected_time_out',
        ]);

    expect(Visit::query()->count())->toBe(0);
});

it('blocks staff from registering walk-ins', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('walk-ins.create'))
        ->assertNotFound();

    $this->actingAs($guard)
        ->post(route('walk-ins.store'), [
            'visitor_contact' => '09181110001',
            'purpose' => 'Test',
            'host_name' => 'Office',
            'expected_time_out' => '16:00',
        ])
        ->assertNotFound();

    expect(Visit::query()->count())->toBe(0);
});
