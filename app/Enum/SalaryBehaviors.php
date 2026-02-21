<?php

namespace App\Enum;

enum SalaryBehaviors: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
