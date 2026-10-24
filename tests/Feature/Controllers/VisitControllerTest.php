<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('shows the visit request form to a visitor', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->get(route('visits.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('visit/Create')
            ->where('defaults.visit_date', now()->toDateString()));
});

it('hides the visit request form from staff', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('visits.create'))
        ->assertNotFound();
});

it('lets a visitor request a visit', function (): void {
    $visitor = User::factory()->create(['contact_number' => '09171234567']);

    $response = $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->addDay()->toDateString(),
        'visitor_contact' => '09171234567',
    ]);

    $visit = Visit::query()->first();

    expect($visit)->not->toBeNull();

    $response->assertRedirectToRoute('visits.show', $visit);

    expect($visit->status)->toBe(VisitStatus::Pending)
        ->and($visit->user_id)->toBe($visitor->id)
        ->and($visit->visitor_name)->toBe($visitor->name)
        ->and($visit->pass_code)->toBeNull()
        ->and($visit->expected_arrival)->toBeNull();
});

it('rejects a visit date in the past', function (): void {
    $visitor = User::factory()->create();

    $response = $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->subDay()->toDateString(),
        'visitor_contact' => '09171234567',
    ]);

    $response->assertSessionHasErrors('visit_date');

    expect(Visit::query()->count())->toBe(0);
});

it('lets a visitor see their pending request without a pass', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->get(route('visits.show', $visit))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('visit/Show')
            ->where('visit.status', VisitStatus::Pending->value)
            ->where('visit.pass_code', null));
});

it('hides another visitor request', function (): void {
    $visitor = User::factory()->create();
    $other = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $other->id]);

    $this->actingAs($visitor)
        ->get(route('visits.show', $visit))
        ->assertNotFound();
});

it('lets a visitor cancel a pending request', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->delete(route('visits.destroy', $visit))
        ->assertRedirectToRoute('visits.index');

    expect(Visit::query()->find($visit->id))->toBeNull();
});

it('does not let a visitor cancel an approved request', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->approved()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->delete(route('visits.destroy', $visit))
        ->assertNotFound();

    expect(Visit::query()->find($visit->id))->not->toBeNull();
});

it('blocks staff from creating visitor requests', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->post(route('visits.store'), [
            'purpose' => 'Test',
            'host_name' => 'Office',
            'visit_date' => now()->toDateString(),
            'visitor_contact' => '09171234567',
        ])
        ->assertNotFound();

    expect(Visit::query()->count())->toBe(0);
});
