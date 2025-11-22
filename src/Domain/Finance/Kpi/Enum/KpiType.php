<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Enum;

use App\Common\DTO\Titleable;

enum KpiType: int implements Titleable
{
    case MONTHLY = 1;
    case BIMONTHLY = 2;
    case QUARTERLY = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::MONTHLY   => 'KPI ежемесячный',
            self::BIMONTHLY => 'KPI спринт (двухмесячный)',
            self::QUARTERLY => 'KPI квартальный',
        };
    }
}
