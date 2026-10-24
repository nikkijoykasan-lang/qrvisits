<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowEmergencyListRequest;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Inertia\Inertia;
use Inertia\Response;

final readonly class EmergencyListController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function __invoke(ShowEmergencyListRequest $request): Response
    {
        $inside = Visit::query()
            ->inside()
            ->with(['checkedInBy'])
            ->orderBy('time_in')
            ->get();

        return Inertia::render('emergency-list/Index', [
            'visits' => $this->presenter->collection($inside),
            'generatedAt' => now()->toIso8601String(),
        ]);
    }
}
