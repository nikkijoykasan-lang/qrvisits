<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;

final readonly class CheckInVisit
{
    public function handle(Visit $visit, User $guard, CarbonInterface $at): Visit
    {
        $visit->forceFill([
            'status' => VisitStatus::Inside,
            'time_in' => $at,
            'checked_in_by' => $guard->id,
        ])->save();

        return $visit->refresh();
    }
}
