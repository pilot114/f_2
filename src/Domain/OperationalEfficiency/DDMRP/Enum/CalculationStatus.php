<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Enum;

use App\Common\DTO\Titleable;

enum CalculationStatus: int implements Titleable
{
    case ADDED = 1;
    case NOT_VERIFIED = 2;
    case EXCLUDED = 0;

    public function getTitle(): string
    {
        return match ($this) {
            self::ADDED        => 'Добавлен',
            self::NOT_VERIFIED => 'Не проверен',
            self::EXCLUDED     => 'Исключён из расчёта DD MRP',
        };
    }
}
