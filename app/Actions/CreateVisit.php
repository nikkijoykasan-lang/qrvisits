<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

final readonly class CreateVisit
{
    /**
     * @param  array{purpose: string, host_name: string, visit_date: string, expected_arrival?: string|null, visitor_contact: string}  $attributes
     */
    public function handle(User $visitor, array $attributes): Visit
    {
        return Visit::query()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $attributes['visitor_contact'],
            'purpose' => $attributes['purpose'],
            'host_name' => $attributes['host_name'],
            'visit_date' => $attributes['visit_date'],
            'expected_arrival' => $attributes['expected_arrival'] ?? null,
            'status' => VisitStatus::Pending,
            'is_walk_in' => false,
        ]);
    }
}
