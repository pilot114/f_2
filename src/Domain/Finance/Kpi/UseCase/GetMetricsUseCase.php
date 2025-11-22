<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use Illuminate\Support\Enumerable;

class GetMetricsUseCase
{
    public function __construct(
        private KpiMetricQueryRepository $readKpiRepo,
    ) {
    }

    public function getMetrics(
        ?string $q = null,
        bool $withDisabled = false
    ): array {
        return $this->readKpiRepo->getMetrics($q, $withDisabled);
    }

    /**
     * @return Enumerable<int, KpiMetric>
     */
    public function getMetricsExtended(
        ?int $groupId = null,
        ?int $metricTypeId = null,
        ?string $q = null,
        bool $withDisabled = false
    ): Enumerable {
        return $this->readKpiRepo->getMetricsExtended($groupId, $metricTypeId, $q, $withDisabled);
    }
}
