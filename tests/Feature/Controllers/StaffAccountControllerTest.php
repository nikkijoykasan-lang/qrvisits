<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

it('lets an admin create a guard account', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('staff.store'), [
        'name' => 'New Guard',
        'email' => 'new.guard@qrvisits.test',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
        'role' => Role::Guard->value,
    ])->assertRedirectToRoute('staff.index');

    $guard = User::query()->where('email', 'new.guard@qrvisits.test')->first();

    expect($guard)->not->toBeNull()
        ->and($guard->role)->toBe(Role::Guard)
        ->and($guard->email_verified_at)->not->toBeNull();
});

it('does not let an admin create a visitor role from the staff screen', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('staff.store'), [
        'name' => 'Sneaky',
        'email' => 'sneaky@qrvisits.test',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
        'role' => Role::Visitor->value,
    ])->assertSessionHasErrors('role');

    expect(User::query()->where('email', 'sneaky@qrvisits.test')->exists())->toBeFalse();
});

it('lets an admin remove a staff account but not themselves', function (): void {
    $admin = User::factory()->admin()->create();
    $guard = User::factory()->guard()->create();

    $this->actingAs($admin)
        ->delete(route('staff.destroy', $guard))
        ->assertRedirectToRoute('staff.index');

    expect(User::query()->find($guard->id))->toBeNull();

    $this->actingAs($admin)
        ->delete(route('staff.destroy', $admin))
        ->assertNotFound();

    expect(User::query()->find($admin->id))->not->toBeNull();
});

it('blocks guards from managing staff', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('staff.index'))
        ->assertNotFound();

    $this->actingAs($guard)
        ->post(route('staff.store'), [
            'name' => 'Fake Admin',
            'email' => 'fake.admin@qrvisits.test',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'role' => Role::Admin->value,
        ])
        ->assertNotFound();
});
