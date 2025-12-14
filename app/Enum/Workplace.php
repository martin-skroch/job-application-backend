<?php

namespace App\Enum;

enum Workplace: string
{
    case Location = 'location';
    case Hybrid = 'hybrid';
    case Remote = 'remote';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
