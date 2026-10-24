<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Visit;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteVisit
{
    public function handle(Visit $visit): void
    {
        if ($visit->id_document_path !== null) {
            Storage::disk('local')->delete($visit->id_document_path);
        }

        $visit->delete();
    }
}
