<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\ExportVisitReportRequest;
use App\Models\Visit;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class VisitExportController
{
    public function __invoke(ExportVisitReportRequest $request): StreamedResponse
    {
        $from = $request->fromDate()->toDateString();
        $to = $request->toDate()->toDateString();

        $visits = Visit::query()
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->orderBy('visit_date')
            ->orderBy('time_in')
            ->get();

        return response()->streamDownload(function () use ($visits): void {
            $handle = fopen('php://output', 'w');
            assert($handle !== false);

            fputcsv($handle, [
                'Visitor',
                'Contact',
                'Purpose',
                'Host',
                'Visit date',
                'Expected arrival',
                'Status',
                'Walk-in',
                'Time in',
                'Time out',
                'Pass code',
            ], ',', '"', '');

            foreach ($visits as $visit) {
                fputcsv($handle, [
                    $visit->visitor_name,
                    $visit->visitor_contact,
                    $visit->purpose,
                    $visit->host_name,
                    $visit->visit_date->toDateString(),
                    $visit->expected_arrival,
                    $visit->status === VisitStatus::Completed ? 'Completed' : $visit->status->label(),
                    $visit->is_walk_in ? 'Yes' : 'No',
                    $visit->time_in?->format('Y-m-d H:i'),
                    $visit->time_out?->format('Y-m-d H:i'),
                    $visit->pass_code,
                ], ',', '"', '');
            }

            fclose($handle);
        }, 'qrvisits-report-'.$from.'-to-'.$to.'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
