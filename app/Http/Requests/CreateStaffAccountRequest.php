<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use App\Rules\ValidEmail;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class CreateStaffAccountRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        return $user->isAdmin()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'max:255',
                'email',
                new ValidEmail,
                Rule::unique(User::class),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in([Role::Guard->value, Role::Admin->value])],
        ];
    }
}
