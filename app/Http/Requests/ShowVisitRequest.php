<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class ShowVisitRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        $visit = $this->route('visit');
        assert($visit instanceof Visit);

        return $user->isStaff() || $visit->user_id === $user->id
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
