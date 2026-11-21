<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class RunDeploymentRequest extends FormRequest
{
    public function authorize(): Response
    {
        $expected = (string) config('deploy.token');
        $given = (string) $this->route('token');

        return $expected !== '' && hash_equals($expected, $given)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * @return array{}
     */
    public function rules(): array
    {
        return [];
    }
}
