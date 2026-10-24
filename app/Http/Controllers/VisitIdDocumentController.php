<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowVisitIdDocumentRequest;
use App\Models\Visit;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class VisitIdDocumentController
{
    public function __invoke(ShowVisitIdDocumentRequest $request, Visit $visit): StreamedResponse
    {
        $path = (string) $visit->id_document_path;
        $disk = Storage::disk('local');

        return $disk->response($path, $visit->id_document_name, [
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
