<?php

namespace App\Enum;

enum BugStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Fixed = 'fixed';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Fixed => 'Fixed',
            self::Rejected => 'Rejected',
            self::Closed => 'Closed',
        };
    }
}
