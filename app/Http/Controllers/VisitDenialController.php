<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DenyVisit;
use App\Http\Requests\CreateVisitDenialRequest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final readonly class VisitDenialController
{
    public function store(
        CreateVisitDenialRequest $request,
        Visit $visit,
        #[CurrentUser] User $user,
        DenyVisit $action,
    ): RedirectResponse {
        $action->handle($visit, $user, $request->string('reason')->value());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('The visit request was denied.'),
        ]);

        return back();
    }
}
