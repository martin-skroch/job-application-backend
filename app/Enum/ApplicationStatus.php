<?php

namespace App\Enum;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Invited = 'invited';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
