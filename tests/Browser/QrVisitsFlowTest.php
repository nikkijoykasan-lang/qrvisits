<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('shows the approved pass on the visitor dashboard', function (): void {
    $visitor = User::factory()->create(['name' => 'Maria Santos']);
    $visit = Visit::factory()->approved()->create([
        'user_id' => $visitor->id,
        'visitor_name' => $visitor->name,
        'purpose' => 'Parent meeting',
    ]);

    $this->actingAs($visitor);

    $page = visit(route('dashboard'));

    $page->assertSee('Your next visit')
        ->assertSee('Parent meeting')
        ->assertSee((string) $visit->pass_code)
        ->assertNoJavaScriptErrors();
});

it('lets a guard scan a pass at the gate', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->approved()->create();

    $this->actingAs($guard);

    $page = visit(route('gate.index'));

    $page->assertSee('Gate scan')
        ->fill('code', (string) $visit->pass_code)
        ->click('Record scan')
        ->assertSee('Checked in. Welcome to campus.')
        ->assertNoJavaScriptErrors();

    expect($visit->refresh()->status)->toBe(VisitStatus::Inside);
});

it('lets an admin approve a visit from the approvals queue', function (): void {
    $admin = User::factory()->admin()->create();
    $visit = Visit::factory()->create(['visitor_name' => 'Ana dela Cruz']);

    $this->actingAs($admin);

    $page = visit(route('approvals.index'));

    $page->assertSee('Ana dela Cruz')
        ->click('Approve')
        ->assertSee('is approved')
        ->assertNoJavaScriptErrors();

    expect($visit->refresh()->status)->toBe(VisitStatus::Approved);
});

it('renders the emergency list for staff', function (): void {
    $guard = User::factory()->guard()->create();
    Visit::factory()->inside()->create(['visitor_name' => 'Ben Lim']);

    $this->actingAs($guard);

    $page = visit(route('emergency-list'));

    $page->assertSee('Emergency visitor list')
        ->assertSee('Ben Lim')
        ->assertNoJavaScriptErrors();
});
