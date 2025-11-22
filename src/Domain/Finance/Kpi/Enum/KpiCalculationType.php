<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Enum;

use App\Common\DTO\Titleable;

enum KpiCalculationType: int implements Titleable
{
    case MANUAL = 1;
    case AUTO = 2;

    public function getTitle(): string
    {
        return match ($this) {
            self::MANUAL => 'ручной расчёт',
            self::AUTO   => 'автоматический расчёт',
        };
    }
}
