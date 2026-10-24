<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DashboardController
{
    public function __construct(private VisitPresenter $presenter)
    {
        //
    }

    public function index(#[CurrentUser] User $user): Response
    {
        return match (true) {
            $user->isAdmin() => $this->admin($user),
            $user->isGuard() => $this->guard($user),
            default => $this->visitor($user),
        };
    }

    private function visitor(User $user): Response
    {
        $nextVisit = $user->visits()
            ->where('status', VisitStatus::Approved->value)
            ->whereDate('visit_date', '>=', now()->toDateString())
            ->orderBy('visit_date')
            ->first();

        return Inertia::render('dashboard/Visitor', [
            'stats' => [
                'pending' => $user->visits()->where('status', VisitStatus::Pending->value)->count(),
                'approved' => $user->visits()->where('status', VisitStatus::Approved->value)->count(),
                'total' => $user->visits()->count(),
            ],
            'nextVisit' => $nextVisit instanceof Visit
                ? $this->presenter->handle($nextVisit, includePass: true)
                : null,
            'recentVisits' => $this->presenter->collection(
                $user->visits()->latest('created_at')->limit(6)->get(),
            ),
        ]);
    }

    private function guard(User $user): Response
    {
        return $this->staffDashboard($user, 'dashboard/Guard');
    }

    private function admin(User $user): Response
    {
        return $this->staffDashboard($user, 'dashboard/Admin');
    }

    private function staffDashboard(User $user, string $page): Response
    {
        $today = now()->toDateString();

        $pending = Visit::query()->pending()->orderBy('created_at')->limit(5)->get();
        $inside = Visit::query()->inside()->orderBy('time_in')->limit(8)->get();

        return Inertia::render($page, [
            'stats' => [
                'pending' => Visit::query()->pending()->count(),
                'inside' => Visit::query()->inside()->count(),
                'expected_today' => Visit::query()->approved()->onDate(now())->count(),
                'completed_today' => Visit::query()->completed()->onDate(now())->count(),
                'walk_ins_today' => Visit::query()
                    ->where('is_walk_in', true)
                    ->whereDate('visit_date', $today)
                    ->count(),
            ],
            'pendingVisits' => $this->presenter->collection($pending),
            'insideVisits' => $this->presenter->collection($inside),
            'canApprove' => $user->isAdmin(),
        ]);
    }
}
