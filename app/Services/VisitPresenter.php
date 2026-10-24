<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VisitStatus;
use App\Models\Visit;

final readonly class VisitPresenter
{
    public function __construct(private QrCodeRenderer $qrCode)
    {
        //
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Visit $visit, bool $includePass = false): array
    {
        $visit->loadMissing(['visitor', 'approver', 'checkedInBy', 'checkedOutBy']);

        return [
            'id' => $visit->id,
            'visitor_name' => $visit->visitor_name,
            'visitor_contact' => $visit->visitor_contact,
            'visitor_email' => $visit->visitor?->email,
            'purpose' => $visit->purpose,
            'host_name' => $visit->host_name,
            'visit_date' => $visit->visit_date->toDateString(),
            'expected_arrival' => $this->timeLabel($visit->expected_arrival),
            'status' => $visit->status->value,
            'status_label' => $visit->status->label(),
            'pass_code' => $visit->pass_code,
            'denial_reason' => $visit->denial_reason,
            'is_walk_in' => $visit->is_walk_in,
            'id_document_name' => $visit->id_document_name,
            'id_document_url' => $visit->id_document_path !== null
                ? route('visits.id-document', $visit)
                : null,
            'approved_at' => $visit->approved_at?->toIso8601String(),
            'approved_by_name' => $visit->approver?->name,
            'time_in' => $visit->time_in?->toIso8601String(),
            'time_out' => $visit->time_out?->toIso8601String(),
            'checked_in_by_name' => $visit->checkedInBy?->name,
            'checked_out_by_name' => $visit->checkedOutBy?->name,
            'created_at' => $visit->created_at->toIso8601String(),
            'can_scan' => $visit->status === VisitStatus::Approved || $visit->status === VisitStatus::Inside,
            'pass_qr' => $includePass && $visit->pass_code !== null
                ? $this->qrCode->handle($visit->pass_code)
                : null,
        ];
    }

    /**
     * @param  iterable<Visit>  $visits
     * @return list<array<string, mixed>>
     */
    public function collection(iterable $visits, bool $includePass = false): array
    {
        $presented = [];

        foreach ($visits as $visit) {
            $presented[] = $this->handle($visit, $includePass);
        }

        return $presented;
    }

    private function timeLabel(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        return mb_substr($time, 0, 5);
    }
}
