<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;

class GetMetricUseCase
{
    public function __construct(
        private KpiMetricQueryRepository $readKpiRepo,
    ) {
    }

    public function getMetric(int $id): ?KpiMetric
    {
        return $this->readKpiRepo->getMetric($id);
    }
}
