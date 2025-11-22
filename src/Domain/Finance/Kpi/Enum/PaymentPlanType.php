<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Enum;

use App\Common\DTO\Titleable;

enum PaymentPlanType: int implements Titleable
{
    case LINEAR = 1;
    case RANGES = 2;

    public function getTitle(): string
    {
        return match ($this) {
            self::LINEAR => 'линейная зависимость',
            self::RANGES => 'деление на диапазоны',
        };
    }
}
