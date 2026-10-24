<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Enums\Role;
use App\Http\Requests\CreateStaffAccountRequest;
use App\Http\Requests\DeleteStaffAccountRequest;
use App\Http\Requests\ListStaffAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StaffAccountController
{
    public function index(ListStaffAccountRequest $request): Response
    {
        $staff = User::query()
            ->whereIn('role', [Role::Guard->value, Role::Admin->value])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'created_at' => $user->created_at->toIso8601String(),
            ]);

        return Inertia::render('staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function create(ListStaffAccountRequest $request): Response
    {
        return Inertia::render('staff/Create', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'roles' => [
                ['value' => Role::Guard->value, 'label' => Role::Guard->label()],
                ['value' => Role::Admin->value, 'label' => Role::Admin->label()],
            ],
        ]);
    }

    public function store(CreateStaffAccountRequest $request, CreateUser $action): RedirectResponse
    {
        $action->handle(
            [
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'role' => $request->string('role')->value(),
                'email_verified_at' => now(),
            ],
            $request->string('password')->value(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Staff account created.'),
        ]);

        return to_route('staff.index');
    }

    public function destroy(DeleteStaffAccountRequest $request, User $user, DeleteUser $action): RedirectResponse
    {
        $action->handle($user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Staff account removed.'),
        ]);

        return to_route('staff.index');
    }
}
