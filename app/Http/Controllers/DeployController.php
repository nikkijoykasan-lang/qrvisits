<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RunDeployment;
use App\Http\Requests\RunDeploymentRequest;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeployController
{
    public function __invoke(RunDeploymentRequest $request, RunDeployment $action): Response
    {
        return response(
            implode("\n\n", $action->handle()),
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }
}
