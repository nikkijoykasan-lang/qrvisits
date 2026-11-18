<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Visit;
use Illuminate\Support\Str;

final readonly class GeneratePassCode
{
    public function handle(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Visit::query()->where('pass_code', $code)->exists());

        return $code;
    }
}
