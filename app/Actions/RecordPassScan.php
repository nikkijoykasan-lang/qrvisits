<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ScanOutcome;
use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;

final readonly class RecordPassScan
{
    public function __construct(
        private CheckInVisit $checkInVisit,
        private CheckOutVisit $checkOutVisit,
    ) {
        //
    }

    /**
     * @return array{outcome: ScanOutcome, visit: Visit|null}
     */
    public function handle(string $code, User $guard, CarbonInterface $at): array
    {
        $visit = Visit::query()
            ->where('pass_code', mb_strtoupper(mb_trim($code)))
            ->first();

        if (! $visit instanceof Visit) {
            return ['outcome' => ScanOutcome::InvalidCode, 'visit' => null];
        }

        $outcome = match (true) {
            $visit->status === VisitStatus::Denied => ScanOutcome::Denied,
            $visit->status === VisitStatus::Pending => ScanOutcome::NotApproved,
            $visit->status === VisitStatus::Completed => ScanOutcome::AlreadyCompleted,
            ! $visit->visit_date->isSameDay($at) => ScanOutcome::WrongDate,
            $visit->status === VisitStatus::Approved => ScanOutcome::CheckedIn,
            default => ScanOutcome::CheckedOut,
        };

        $visit = match ($outcome) {
            ScanOutcome::CheckedIn => $this->checkInVisit->handle($visit, $guard, $at),
            ScanOutcome::CheckedOut => $this->checkOutVisit->handle($visit, $guard, $at),
            default => $visit,
        };

        return ['outcome' => $outcome, 'visit' => $visit];
    }
}
