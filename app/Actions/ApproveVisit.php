<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class ApproveVisit
{
    public function handle(Visit $visit, User $approver): Visit
    {
        return DB::transaction(function () use ($visit, $approver): Visit {
            $visit->forceFill([
                'status' => VisitStatus::Approved,
                'pass_code' => $visit->pass_code ?? $this->generatePassCode(),
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'denial_reason' => null,
            ])->save();

            return $visit->refresh();
        });
    }

    private function generatePassCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Visit::query()->where('pass_code', $code)->exists());

        return $code;
    }
}
