<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowGateRequest;
use App\Models\Visit;
use Inertia\Inertia;
use Inertia\Response;

final readonly class GateController
{
    public function index(ShowGateRequest $request): Response
    {
        return Inertia::render('gate/Scan', [
            'stats' => [
                'inside' => Visit::query()->inside()->count(),
                'expected_today' => Visit::query()->approved()->onDate(now())->count(),
                'completed_today' => Visit::query()->completed()->onDate(now())->count(),
            ],
        ]);
    }
}
