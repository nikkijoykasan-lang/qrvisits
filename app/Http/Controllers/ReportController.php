<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\ListReportRequest;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ReportController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function index(ListReportRequest $request): Response
    {
        $from = $request->fromDate();
        $to = $request->toDate();

        $visits = $this->query($from->toDateString(), $to->toDateString())
            ->with(['visitor', 'approver', 'checkedInBy', 'checkedOutBy'])
            ->orderBy('visit_date')
            ->orderBy('time_in')
            ->limit(500)
            ->get();

        $durations = [];

        foreach ($visits as $visit) {
            if ($visit->time_in instanceof CarbonInterface && $visit->time_out instanceof CarbonInterface) {
                $durations[] = (int) $visit->time_in->diffInMinutes($visit->time_out);
            }
        }

        $averageMinutes = $durations === []
            ? null
            : (int) round(array_sum($durations) / count($durations));

        return Inertia::render('report/Index', [
            'visits' => $this->presenter->collection($visits),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'total' => $visits->count(),
                'completed' => $visits->where('status', VisitStatus::Completed)->count(),
                'inside' => $visits->where('status', VisitStatus::Inside)->count(),
                'pending' => $visits->where('status', VisitStatus::Pending)->count(),
                'denied' => $visits->where('status', VisitStatus::Denied)->count(),
                'walk_ins' => $visits->where('is_walk_in', true)->count(),
                'average_minutes' => $averageMinutes,
            ],
        ]);
    }

    /**
     * @return Builder<Visit>
     */
    private function query(string $from, string $to): Builder
    {
        return Visit::query()
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to);
    }
}
