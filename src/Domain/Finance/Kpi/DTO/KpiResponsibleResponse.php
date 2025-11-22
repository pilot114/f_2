<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

class KpiResponsibleResponse
{
    public function __construct(
        public int $id,
        public array $user,
        public array $enterprise,
    ) {
    }
}
