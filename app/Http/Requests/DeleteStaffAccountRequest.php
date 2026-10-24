<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteStaffAccountRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        $target = $this->route('user');
        assert($target instanceof User);

        return $user->isAdmin() && $target->isStaff() && $target->id !== $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
