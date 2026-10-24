<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Visitor = 'visitor';
    case Guard = 'guard';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Visitor => 'Visitor',
            self::Guard => 'Guard',
            self::Admin => 'Admin',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Visitor;
    }
}
