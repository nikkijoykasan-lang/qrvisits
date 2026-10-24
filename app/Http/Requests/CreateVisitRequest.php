<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class CreateVisitRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        return $user->isVisitor()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'max:255'],
            'host_name' => ['required', 'string', 'max:255'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'visitor_contact' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
        ];
    }
}
