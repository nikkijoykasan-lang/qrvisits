<?php

declare(strict_types=1);

use App\Models\User;

it('shows the database design page to authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('context.erd'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('context/Erd'));
});

it('requires login to see the database design page', function (): void {
    $this->get(route('context.erd'))
        ->assertRedirectToRoute('login');
});
