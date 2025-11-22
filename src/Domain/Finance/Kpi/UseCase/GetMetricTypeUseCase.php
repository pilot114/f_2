<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;

class GetMetricTypeUseCase
{
    public function __construct(
        private KpiMetricQueryRepository $readKpiRepo,
    ) {
    }

    public function getMetricType(int $id): ?KpiMetricType
    {
        return $this->readKpiRepo->getMetricType($id);
    }
}
