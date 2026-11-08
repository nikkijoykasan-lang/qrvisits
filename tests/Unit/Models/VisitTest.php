<?php

declare(strict_types=1);

use App\Models\Visit;

it('only treats approved passes as usable on their date', function (): void {
    $today = Visit::factory()->approved()->create([
        'visit_date' => now()->toDateString(),
    ]);

    $tomorrow = Visit::factory()->approved()->create([
        'visit_date' => now()->addDay()->toDateString(),
    ]);

    expect($today->isUsableOn(now()))->toBeTrue()
        ->and($tomorrow->isUsableOn(now()))->toBeFalse();
});

it('does not treat pending or completed passes as usable', function (): void {
    $pending = Visit::factory()->create();
    $completed = Visit::factory()->completed()->create();

    expect($pending->isUsableOn(now()))->toBeFalse()
        ->and($completed->isUsableOn(now()))->toBeFalse();
});

it('marks an inside visitor as overdue after the expected time-out', function (): void {
    $visit = Visit::factory()->inside()->create([
        'visit_date' => now()->toDateString(),
        'expected_time_out' => now()->subMinutes(30)->format('H:i'),
    ]);

    expect($visit->isOverdue())->toBeTrue()
        ->and($visit->overdueMinutes())->toBeGreaterThanOrEqual(29);
});

it('does not mark a visitor as overdue before the expected time-out', function (): void {
    $visit = Visit::factory()->inside()->create([
        'visit_date' => now()->toDateString(),
        'expected_time_out' => now()->addHour()->format('H:i'),
    ]);

    expect($visit->isOverdue())->toBeFalse()
        ->and($visit->overdueMinutes())->toBe(0);
});

it('does not mark a checked-out visitor as overdue', function (): void {
    $visit = Visit::factory()->completed()->create([
        'expected_time_out' => now()->subHour()->format('H:i'),
    ]);

    expect($visit->isOverdue())->toBeFalse();
});
