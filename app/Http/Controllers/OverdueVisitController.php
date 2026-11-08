<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ListOverdueVisits;
use App\Http\Requests\ListOverdueVisitRequest;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;

final readonly class OverdueVisitController
{
    public function __invoke(ListOverdueVisitRequest $request, ListOverdueVisits $action): JsonResponse
    {
        $visits = $action->handle(now());

        return response()->json([
            'count' => $visits->count(),
            'visits' => $visits->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'visitor_name' => $visit->visitor_name,
                'pass_code' => $visit->pass_code,
                'expected_time_out' => mb_substr((string) $visit->expected_time_out, 0, 5),
                'time_in' => $visit->time_in?->toIso8601String(),
                'minutes_overdue' => $visit->overdueMinutes(),
                'visit_url' => route('visits.show', $visit),
            ])->values(),
        ]);
    }
}
