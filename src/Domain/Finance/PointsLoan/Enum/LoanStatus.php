<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Enum;

use App\Common\DTO\Titleable;

enum LoanStatus: int implements Titleable
{
    case PAID = 1;
    case NOT_PAID = 2;

    public function getTitle(): string
    {
        return match ($this) {
            self::PAID     => 'Погашен',
            self::NOT_PAID => 'Не погашен',
        };
    }
}
