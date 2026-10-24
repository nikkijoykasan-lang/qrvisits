<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;

final readonly class CheckOutVisit
{
    public function handle(Visit $visit, User $guard, CarbonInterface $at): Visit
    {
        $visit->forceFill([
            'status' => VisitStatus::Completed,
            'time_out' => $at,
            'checked_out_by' => $guard->id,
        ])->save();

        return $visit->refresh();
    }
}
