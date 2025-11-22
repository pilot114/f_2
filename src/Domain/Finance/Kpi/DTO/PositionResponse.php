<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use DateTimeImmutable;

readonly class PositionResponse
{
    public function __construct(
        public ?int $id,
        public ?string $name, // Наименование штатной единицы
        public bool $isMain, // основная должность
        public ?DateTimeImmutable $fromDate, // Дата назначения
    ) {
    }
}
