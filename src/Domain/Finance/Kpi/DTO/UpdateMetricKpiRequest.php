<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

readonly class UpdateMetricKpiRequest
{
    public function __construct(
        public int $id,
        public int $factual,
        public int $plan,
        public float $weight,
    ) {
    }
}
