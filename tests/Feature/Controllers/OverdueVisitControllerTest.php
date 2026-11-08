<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Visit;

it('reports visitors who are still inside after their expected time-out', function (): void {
    $guard = User::factory()->guard()->create();

    Visit::factory()->inside()->create([
        'visitor_name' => 'Ana dela Cruz',
        'pass_code' => 'OVD12345',
        'expected_time_out' => now()->subHour()->format('H:i'),
    ]);

    Visit::factory()->inside()->create([
        'visitor_name' => 'Ben Lim',
        'expected_time_out' => now()->addHour()->format('H:i'),
    ]);

    Visit::factory()->approved()->create([
        'expected_time_out' => now()->subHour()->format('H:i'),
    ]);

    Visit::factory()->completed()->create([
        'expected_time_out' => now()->subHour()->format('H:i'),
    ]);

    $this->actingAs($guard)
        ->getJson(route('alerts.overdue'))
        ->assertOk()
        ->assertJson(['count' => 1])
        ->assertJsonPath('visits.0.visitor_name', 'Ana dela Cruz')
        ->assertJsonPath('visits.0.pass_code', 'OVD12345')
        ->assertJsonStructure([
            'count',
            'visits' => [[
                'id',
                'visitor_name',
                'pass_code',
                'expected_time_out',
                'time_in',
                'minutes_overdue',
                'visit_url',
            ]],
        ]);
});

it('shows no alerts when nobody is overdue', function (): void {
    $admin = User::factory()->admin()->create();

    Visit::factory()->inside()->create([
        'expected_time_out' => now()->addHour()->format('H:i'),
    ]);

    $this->actingAs($admin)
        ->getJson(route('alerts.overdue'))
        ->assertOk()
        ->assertJson(['count' => 0, 'visits' => []]);
});

it('hides overdue alerts from visitors', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->getJson(route('alerts.overdue'))
        ->assertNotFound();
});
