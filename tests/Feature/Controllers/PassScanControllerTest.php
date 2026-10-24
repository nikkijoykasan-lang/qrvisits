<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

it('checks a visitor in on the first scan', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->approved()->create();

    $response = $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => $visit->pass_code]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'outcome' => 'checked_in',
        ]);

    $visit->refresh();

    expect($visit->status)->toBe(VisitStatus::Inside)
        ->and($visit->time_in)->not->toBeNull()
        ->and($visit->checked_in_by)->toBe($guard->id)
        ->and($visit->time_out)->toBeNull();
});

it('checks the visitor out on the second scan', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->approved()->create();

    $this->actingAs($guard)->postJson(route('gate.scan'), ['code' => $visit->pass_code]);

    $response = $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => $visit->pass_code]);

    $response->assertOk()->assertJson([
        'success' => true,
        'outcome' => 'checked_out',
    ]);

    $visit->refresh();

    expect($visit->status)->toBe(VisitStatus::Completed)
        ->and($visit->time_out)->not->toBeNull()
        ->and($visit->checked_out_by)->toBe($guard->id);
});

it('accepts a lowercase code with extra spaces', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->approved()->create();

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => '  '.mb_strtolower((string) $visit->pass_code).' '])
        ->assertJson(['outcome' => 'checked_in']);
});

it('refuses an unknown pass code', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => 'NOTREAL1'])
        ->assertOk()
        ->assertJson([
            'success' => false,
            'outcome' => 'invalid_code',
            'visit' => null,
        ]);
});

it('refuses a pass that is still pending approval', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->create(['pass_code' => 'PENDING1']);

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => 'PENDING1'])
        ->assertJson(['outcome' => 'not_approved']);

    expect($visit->refresh()->status)->toBe(VisitStatus::Pending);
});

it('refuses a denied pass', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->denied()->create(['pass_code' => 'DENIED01']);

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => 'DENIED01'])
        ->assertJson(['outcome' => 'denied']);

    expect($visit->refresh()->status)->toBe(VisitStatus::Denied);
});

it('refuses a pass used on the wrong date', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->approved()->create([
        'visit_date' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => $visit->pass_code])
        ->assertJson(['outcome' => 'wrong_date']);

    expect($visit->refresh()->status)->toBe(VisitStatus::Approved);
});

it('refuses a pass that has already been completed', function (): void {
    $guard = User::factory()->guard()->create();
    $visit = Visit::factory()->completed()->create();

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), ['code' => $visit->pass_code])
        ->assertJson(['outcome' => 'already_completed']);
});

it('requires a pass code', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->postJson(route('gate.scan'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('blocks visitors from scanning passes', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->postJson(route('gate.scan'), ['code' => 'ANYCODE1'])
        ->assertNotFound();
});
