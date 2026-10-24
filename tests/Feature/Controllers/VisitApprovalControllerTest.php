<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('lets an admin approve a pending visit and issue a pass', function (): void {
    $admin = User::factory()->admin()->create();
    $visit = Visit::factory()->create();

    $this->actingAs($admin)
        ->post(route('visits.approval.store', $visit))
        ->assertRedirect();

    $visit->refresh();

    expect($visit->status)->toBe(VisitStatus::Approved)
        ->and($visit->pass_code)->not->toBeNull()
        ->and($visit->approved_by)->toBe($admin->id)
        ->and($visit->approved_at)->not->toBeNull();
});

it('does not let a guard approve a visit', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->create();

    $this->actingAs($guard)
        ->post(route('visits.approval.store', $visit))
        ->assertNotFound();

    expect($visit->refresh()->status)->toBe(VisitStatus::Pending);
});

it('does not let a visitor approve their own visit', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->post(route('visits.approval.store', $visit))
        ->assertNotFound();

    expect($visit->refresh()->status)->toBe(VisitStatus::Pending);
});

it('lets an admin deny a visit with a reason', function (): void {
    $admin = User::factory()->admin()->create();
    $visit = Visit::factory()->create();

    $this->actingAs($admin)
        ->post(route('visits.denial.store', $visit), [
            'reason' => 'Incomplete details.',
        ])
        ->assertRedirect();

    $visit->refresh();

    expect($visit->status)->toBe(VisitStatus::Denied)
        ->and($visit->denial_reason)->toBe('Incomplete details.')
        ->and($visit->pass_code)->toBeNull();
});

it('requires a reason when denying', function (): void {
    $admin = User::factory()->admin()->create();
    $visit = Visit::factory()->create();

    $this->actingAs($admin)
        ->post(route('visits.denial.store', $visit), [])
        ->assertSessionHasErrors('reason');

    expect($visit->refresh()->status)->toBe(VisitStatus::Pending);
});

it('does not allow approving the same visit twice', function (): void {
    $admin = User::factory()->admin()->create();
    $visit = Visit::factory()->approved()->create();
    $passCode = $visit->pass_code;

    $this->actingAs($admin)
        ->post(route('visits.approval.store', $visit))
        ->assertNotFound();

    expect($visit->refresh()->pass_code)->toBe($passCode);
});

it('shows the approvals queue to an admin only', function (): void {
    $admin = User::factory()->admin()->create();
    $guard = User::factory()->guard()->create();

    Visit::factory()->create();

    $this->actingAs($admin)
        ->get(route('approvals.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('visit/Approvals')
            ->has('visits', 1));

    $this->actingAs($guard)
        ->get(route('approvals.index'))
        ->assertNotFound();
});
