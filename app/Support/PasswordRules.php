<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rules\Password;
use ReflectionClass;

/**
 * Laravel 13 ships Password::toPasswordRulesString(), which is unavailable on
 * Laravel 12. This reproduces it for the front end's password hints.
 */
final class PasswordRules
{
    public static function toHtmlString(?Password $password = null): string
    {
        $password ??= Password::min(8);

        $reflection = new ReflectionClass($password);

        $read = static fn (string $property): mixed => $reflection->getProperty($property)->getValue($password);

        $integer = static fn (string $property): int => is_int($value = $read($property)) ? $value : 0;

        $boolean = static fn (string $property): bool => $read($property) === true;

        $min = $integer('min');
        $max = $integer('max');

        $rules = ['minlength: '.$min];

        if ($max > 0) {
            $rules[] = 'maxlength: '.$max;
        }

        if ($boolean('mixedCase')) {
            $rules[] = 'required: lower';
            $rules[] = 'required: upper';
        } elseif ($boolean('letters')) {
            $rules[] = 'required: lower';
        }

        if ($boolean('numbers')) {
            $rules[] = 'required: digit';
        }

        if ($boolean('symbols')) {
            $rules[] = 'required: special';
        }

        return implode('; ', $rules).';';
    }
}
