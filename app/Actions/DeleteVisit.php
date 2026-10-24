<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Visit;

final readonly class DeleteVisit
{
    public function handle(Visit $visit): void
    {
        $visit->delete();
    }
}
