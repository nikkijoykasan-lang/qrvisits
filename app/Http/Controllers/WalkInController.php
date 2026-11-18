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
    public function create(ShowWalkInCreateRequest $request, #[CurrentUser] User $user): Response
    {
        return Inertia::render('walk-in/Create', [
            'visitor' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->contact_number ?? '',
            ],
            'defaults' => [
                'expected_time_out' => now()->addHours(2)->format('H:i'),
            ],
        ]);
    }

    public function store(CreateWalkInRequest $request, #[CurrentUser] User $user, CreateWalkIn $action): RedirectResponse
    {
        /** @var array{purpose: string, host_name: string, visitor_contact: string, expected_time_out: string} $attributes */
        $attributes = $request->safe()->only([
            'purpose',
            'host_name',
            'visitor_contact',
            'expected_time_out',
        ]);

        $visit = $action->handle($user, $attributes);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Walk-in pass issued. Show the QR code at the gate.'),
        ]);

        return to_route('visits.show', $visit);
    }
}
