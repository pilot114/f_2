<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use DateTimeImmutable;

class DeputyResponse
{
    public function __construct(
        public int $id,
        public DateTimeImmutable $dateStart,
        public DateTimeImmutable $dateEnd,
        public array $deputyUser,
    ) {
    }
}
