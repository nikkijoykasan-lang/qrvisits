<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordPassScan;
use App\Http\Requests\CreatePassScanRequest;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitPresenter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class PassScanController
{
    public function __invoke(
        CreatePassScanRequest $request,
        #[CurrentUser] User $user,
        RecordPassScan $action,
        VisitPresenter $presenter,
    ): JsonResponse {
        $result = $action->handle($request->string('code')->value(), $user, now());

        return response()->json([
            'success' => $result['outcome']->isSuccess(),
            'outcome' => $result['outcome']->value,
            'message' => $result['outcome']->message(),
            'visit' => $result['visit'] instanceof Visit
                ? $presenter->handle($result['visit'])
                : null,
        ]);
    }
}
