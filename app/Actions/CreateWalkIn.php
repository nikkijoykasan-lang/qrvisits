<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

final readonly class CreateWalkIn
{
    /**
     * @param  array{visitor_name: string, visitor_contact: string, purpose: string, host_name: string, expected_arrival?: string|null}  $attributes
     */
    public function handle(User $guard, array $attributes): Visit
    {
        return Visit::query()->create([
            'user_id' => null,
            'visitor_name' => $attributes['visitor_name'],
            'visitor_contact' => $attributes['visitor_contact'],
            'purpose' => $attributes['purpose'],
            'host_name' => $attributes['host_name'],
            'visit_date' => now()->toDateString(),
            'expected_arrival' => $attributes['expected_arrival'] ?? now()->format('H:i'),
            'status' => VisitStatus::Pending,
            'is_walk_in' => true,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }
}
