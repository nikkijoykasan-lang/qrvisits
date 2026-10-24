<?php

declare(strict_types=1);

namespace App\Enums;

enum VisitStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Inside = 'inside';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending approval',
            self::Approved => 'Approved',
            self::Denied => 'Denied',
            self::Inside => 'Inside campus',
            self::Completed => 'Completed',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Approved, self::Inside], true);
    }
}
