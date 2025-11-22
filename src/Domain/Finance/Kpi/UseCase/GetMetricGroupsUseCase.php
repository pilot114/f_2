<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use Illuminate\Support\Enumerable;

class GetMetricGroupsUseCase
{
    public function __construct(
        private KpiMetricQueryRepository $readKpiRepo,
    ) {
    }

    /**
     * @return Enumerable<int, KpiMetricGroup>
     */
    public function getMetricGroups(
    ): Enumerable {
        return $this->readKpiRepo->getMetricGroups();
    }
}
