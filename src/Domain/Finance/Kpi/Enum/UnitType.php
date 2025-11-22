<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Enum;

use App\Common\DTO\Titleable;

enum UnitType: int implements Titleable
{
    case PIECES = 1;
    case PERCENTS = 2;
    case CONDITIONAL_UNITS = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::PIECES            => 'штуки',
            self::PERCENTS          => '%',
            self::CONDITIONAL_UNITS => 'усл. ед.',
        };
    }
}
