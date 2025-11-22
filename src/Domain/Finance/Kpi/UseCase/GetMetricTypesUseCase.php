<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use Illuminate\Support\Enumerable;

class GetMetricTypesUseCase
{
    public function __construct(
        private KpiMetricQueryRepository $readKpiRepo,
    ) {
    }

    public function getMetricTypes(?string $q = null, bool $withDisabled = false): array
    {
        return $this->readKpiRepo->getMetricTypes($q, $withDisabled);
    }

    /**
     * @return Enumerable<int, KpiMetricType>
     */
    public function getMetricTypesExtended(?string $q = null, bool $withDisabled = false): Enumerable
    {
        return $this->readKpiRepo->getMetricTypesExtends($q, $withDisabled);
    }
}
