<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class ListVisitRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->user();
        assert($user instanceof User);

        return Response::allow();
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'max:20'],
            'date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function statusFilter(): ?string
    {
        $status = $this->query('status');

        return is_string($status) && $status !== '' ? $status : null;
    }

    public function dateFilter(): ?string
    {
        $date = $this->query('date');

        return is_string($date) && $date !== '' ? $date : null;
    }

    public function searchTerm(): ?string
    {
        $search = $this->query('search');

        return is_string($search) && mb_trim($search) !== '' ? mb_trim($search) : null;
    }
}
