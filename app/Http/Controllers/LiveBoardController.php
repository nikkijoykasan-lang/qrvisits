<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowLiveBoardRequest;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Inertia\Inertia;
use Inertia\Response;

final readonly class LiveBoardController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function index(ShowLiveBoardRequest $request): Response
    {
        $inside = Visit::query()
            ->inside()
            ->with(['checkedInBy'])
            ->orderBy('time_in')
            ->get();

        $expected = Visit::query()
            ->approved()
            ->onDate(now())
            ->orderBy('expected_arrival')
            ->limit(50)
            ->get();

        return Inertia::render('gate/Board', [
            'visits' => $this->presenter->collection($inside),
            'expected' => $this->presenter->collection($expected),
            'stats' => [
                'inside' => $inside->count(),
                'expected_today' => $expected->count(),
                'completed_today' => Visit::query()->completed()->onDate(now())->count(),
            ],
        ]);
    }
}
