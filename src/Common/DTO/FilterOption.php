<?php

declare(strict_types=1);

namespace App\Common\DTO;

enum FilterOption: string
{
    case Q_ANY = 'Q_ANY';
    case Q_SOME = 'Q_SOME';
    case Q_NONE = 'Q_NONE';

    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }
}
