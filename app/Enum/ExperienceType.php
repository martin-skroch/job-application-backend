<?php

namespace App\Enum;

enum ExperienceType: string
{
    case Work = 'work';
    case Education = 'education';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
