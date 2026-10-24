<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ApproveVisit;
use App\Http\Requests\CreateVisitApprovalRequest;
use App\Http\Requests\ListVisitApprovalRequest;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class VisitApprovalController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function index(ListVisitApprovalRequest $request): Response
    {
        $pending = Visit::query()
            ->pending()
            ->with(['visitor'])
            ->orderBy('created_at')
            ->get();

        return Inertia::render('visit/Approvals', [
            'visits' => $this->presenter->collection($pending),
            'counts' => [
                'pending' => $pending->count(),
                'walk_ins' => $pending->where('is_walk_in', true)->count(),
            ],
        ]);
    }

    public function store(
        CreateVisitApprovalRequest $request,
        Visit $visit,
        #[CurrentUser] User $user,
        ApproveVisit $action,
    ): RedirectResponse {
        $action->handle($visit, $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':name is approved. The QR pass is ready.', ['name' => $visit->visitor_name]),
        ]);

        return back();
    }
}
