<?php

declare(strict_types=1);

namespace App\Enums;

enum ScanOutcome: string
{
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case InvalidCode = 'invalid_code';
    case NotApproved = 'not_approved';
    case Denied = 'denied';
    case WrongDate = 'wrong_date';
    case AlreadyCompleted = 'already_completed';

    public function isSuccess(): bool
    {
        return in_array($this, [self::CheckedIn, self::CheckedOut], true);
    }

    public function message(): string
    {
        return match ($this) {
            self::CheckedIn => 'Checked in. Welcome to campus.',
            self::CheckedOut => 'Checked out. Have a safe trip.',
            self::InvalidCode => 'Pass not found. Check the code or register a walk-in.',
            self::NotApproved => 'This pass has not been approved yet.',
            self::Denied => 'This visit request was denied.',
            self::WrongDate => 'This pass is not valid today.',
            self::AlreadyCompleted => 'This visitor has already completed their visit.',
        };
    }
}
