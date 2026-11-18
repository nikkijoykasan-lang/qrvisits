<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

final readonly class ApproveVisit
{
    public function __construct(private GeneratePassCode $passCodes)
    {
        //
    }

    public function handle(Visit $visit, User $approver): Visit
    {
        return DB::transaction(function () use ($visit, $approver): Visit {
            $visit->forceFill([
                'status' => VisitStatus::Approved,
                'pass_code' => $visit->pass_code ?? $this->passCodes->handle(),
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'denial_reason' => null,
            ])->save();

            return $visit->refresh();
        });
    }
}
