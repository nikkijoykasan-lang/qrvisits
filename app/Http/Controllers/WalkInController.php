<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateWalkIn;
use App\Http\Requests\CreateWalkInRequest;
use App\Http\Requests\ShowWalkInCreateRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class WalkInController
{
    public function create(ShowWalkInCreateRequest $request): Response
    {
        return Inertia::render('walk-in/Create', [
            'defaults' => [
                'expected_arrival' => now()->format('H:i'),
                'expected_time_out' => now()->addHours(2)->format('H:i'),
            ],
        ]);
    }

    public function store(CreateWalkInRequest $request, #[CurrentUser] User $user, CreateWalkIn $action): RedirectResponse
    {
        /** @var array{visitor_name: string, visitor_contact: string, purpose: string, host_name: string, expected_arrival?: string|null, expected_time_out: string} $attributes */
        $attributes = $request->safe()->only([
            'visitor_name',
            'visitor_contact',
            'purpose',
            'host_name',
            'expected_arrival',
            'expected_time_out',
        ]);

        $visit = $action->handle($user, $attributes);

        Inertia::flash('toast', [
            'type' => 'info',
            'message' => __('Walk-in registered. An admin must approve it before the visitor can enter.'),
        ]);

        return to_route('visits.show', $visit);
    }
}
