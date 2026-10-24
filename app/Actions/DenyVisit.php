<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;

final readonly class DenyVisit
{
    public function handle(Visit $visit, User $approver, string $reason): Visit
    {
        $visit->forceFill([
            'status' => VisitStatus::Denied,
            'denial_reason' => $reason,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'pass_code' => null,
        ])->save();

        return $visit->refresh();
    }
}
