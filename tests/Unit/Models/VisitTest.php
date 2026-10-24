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
