<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;

readonly class MetricResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public KpiType $kpiType,
        public KpiCalculationType $calculationType,
        public string $calculationTypeDescription,
        public array $group,
        public array $type,
        public UnitType $unitType,
        public array $departments = [],
    ) {
    }
}
