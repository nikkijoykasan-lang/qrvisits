<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteVisitRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        $visit = $this->route('visit');
        assert($visit instanceof Visit);

        return $visit->user_id === $user->id && $visit->status === VisitStatus::Pending
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
