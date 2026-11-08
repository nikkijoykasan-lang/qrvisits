<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class ListOverdueVisits
{
    /**
     * @return Collection<int, Visit>
     */
    public function handle(CarbonInterface $at): Collection
    {
        return Visit::query()
            ->inside()
            ->whereNotNull('expected_time_out')
            ->orderBy('expected_time_out')
            ->get()
            ->filter(fn (Visit $visit): bool => $visit->isOverdue($at))
            ->values();
    }
}
