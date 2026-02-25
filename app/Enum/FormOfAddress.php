<?php

namespace App\Enum;

enum FormOfAddress: string
{
    case Formal = 'formal';
    case Informal = 'informal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
