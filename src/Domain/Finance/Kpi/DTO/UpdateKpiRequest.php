<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

readonly class UpdateKpiRequest
{
    public function __construct(
        public int $id,
        public int $empId,
        public ?int $value,
        public ?int $valueCalculated = null,
        /** @var array<UpdateMetricKpiRequest> */
        public array $metrics = [],
    ) {
    }
}
