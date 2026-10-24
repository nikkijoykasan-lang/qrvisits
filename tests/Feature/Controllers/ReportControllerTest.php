<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Visit;

it('shows the report summary to an admin', function (): void {
    $admin = User::factory()->admin()->create();

    Visit::factory()->completed()->create(['visit_date' => now()->toDateString()]);
    Visit::factory()->create(['visit_date' => now()->toDateString()]);

    $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('report/Index')
            ->where('summary.total', 2)
            ->where('summary.completed', 1)
            ->where('summary.pending', 1));
});

it('blocks guards from reports', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('reports.index'))
        ->assertNotFound();
});

it('exports visits as a csv file', function (): void {
    $admin = User::factory()->admin()->create();

    Visit::factory()->create([
        'visitor_name' => 'Maria Santos',
        'visit_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('Maria Santos')
        ->toContain('Visitor,Contact,Purpose');
});

it('blocks guards from exporting reports', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('reports.export'))
        ->assertNotFound();
});
