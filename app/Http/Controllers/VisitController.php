<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVisit;
use App\Actions\DeleteVisit;
use App\Http\Requests\CreateVisitRequest;
use App\Http\Requests\DeleteVisitRequest;
use App\Http\Requests\ListVisitRequest;
use App\Http\Requests\ShowVisitCreateRequest;
use App\Http\Requests\ShowVisitRequest;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class VisitController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function index(ListVisitRequest $request, #[CurrentUser] User $user): Response
    {
        $query = Visit::query()
            ->with(['visitor', 'approver', 'checkedInBy', 'checkedOutBy'])
            ->latest('visit_date')
            ->latest('created_at');

        if ($user->isVisitor()) {
            $query->where('user_id', $user->id);
        }

        if (($status = $request->statusFilter()) !== null) {
            $query->where('status', $status);
        }

        if (($date = $request->dateFilter()) !== null) {
            $query->whereDate('visit_date', $date);
        }

        if (($search = $request->searchTerm()) !== null) {
            $query->where(function ($query) use ($search): void {
                $query->where('visitor_name', 'like', '%'.$search.'%')
                    ->orWhere('host_name', 'like', '%'.$search.'%')
                    ->orWhere('pass_code', 'like', '%'.$search.'%');
            });
        }

        $visits = $query->paginate(20)->withQueryString();

        return Inertia::render('visit/Index', [
            'visits' => $this->presenter->collection($visits->items()),
            'pagination' => [
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
                'previous_page_url' => $visits->previousPageUrl(),
                'next_page_url' => $visits->nextPageUrl(),
            ],
            'filters' => [
                'status' => $request->statusFilter(),
                'date' => $request->dateFilter(),
                'search' => $request->searchTerm(),
            ],
            'isVisitor' => $user->isVisitor(),
        ]);
    }

    public function create(ShowVisitCreateRequest $request, #[CurrentUser] User $user): Response
    {
        return Inertia::render('visit/Create', [
            'defaults' => [
                'visitor_contact' => $user->contact_number ?? '',
                'visit_date' => now()->toDateString(),
            ],
        ]);
    }

    public function store(CreateVisitRequest $request, #[CurrentUser] User $user, CreateVisit $action): RedirectResponse
    {
        /** @var array{purpose: string, host_name: string, visit_date: string, visitor_contact: string} $attributes */
        $attributes = $request->safe()->only([
            'purpose',
            'host_name',
            'visit_date',
            'visitor_contact',
        ]);

        $visit = $action->handle($user, $attributes);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Visit request submitted. You will see the pass here once it is approved.'),
        ]);

        return to_route('visits.show', $visit);
    }

    public function show(ShowVisitRequest $request, Visit $visit): Response
    {
        $user = $request->user();
        assert($user instanceof User);

        return Inertia::render('visit/Show', [
            'visit' => $this->presenter->handle($visit, includePass: true),
            'isVisitor' => $user->isVisitor(),
            'canApprove' => $user->isAdmin(),
        ]);
    }

    public function destroy(DeleteVisitRequest $request, Visit $visit, DeleteVisit $action): RedirectResponse
    {
        $action->handle($visit);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Visit request cancelled.'),
        ]);

        return to_route('visits.index');
    }
}
