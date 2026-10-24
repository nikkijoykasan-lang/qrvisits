<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class ListReportRequest extends FormRequest
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
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function fromDate(): CarbonImmutable
    {
        $from = $this->query('from');

        return is_string($from) && $from !== ''
            ? CarbonImmutable::parse($from)->startOfDay()
            : CarbonImmutable::now()->startOfMonth();
    }

    public function toDate(): CarbonImmutable
    {
        $to = $this->query('to');

        return is_string($to) && $to !== ''
            ? CarbonImmutable::parse($to)->startOfDay()
            : CarbonImmutable::now()->startOfDay();
    }
}
