<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Enum;

use App\Common\DTO\Titleable;

enum ViolationType: int implements Titleable
{
    case UNDER_CONTROL = 42;
    case DO_NOT_ISSUE = 41;

    public function getTitle(): string
    {
        return match ($this) {
            self::UNDER_CONTROL => 'На контроле',
            self::DO_NOT_ISSUE  => 'Не выдаем',
        };
    }
}
