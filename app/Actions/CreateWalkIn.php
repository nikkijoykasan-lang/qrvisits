<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

final readonly class CreateWalkIn
{
    public function __construct(private GeneratePassCode $passCodes)
    {
        //
    }

    /**
     * @param  array{purpose: string, host_name: string, visitor_contact: string, expected_time_out: string}  $attributes
     */
    public function handle(User $visitor, array $attributes): Visit
    {
        return Visit::query()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $attributes['visitor_contact'],
            'purpose' => $attributes['purpose'],
            'host_name' => $attributes['host_name'],
            'visit_date' => now()->toDateString(),
            'expected_arrival' => now()->format('H:i'),
            'expected_time_out' => $attributes['expected_time_out'],
            'status' => VisitStatus::Approved,
            'pass_code' => $this->passCodes->handle(),
            'is_walk_in' => true,
            'approved_at' => now(),
        ]);
    }
}
