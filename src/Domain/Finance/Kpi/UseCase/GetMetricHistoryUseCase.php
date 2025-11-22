<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use Illuminate\Support\Enumerable;

class GetMetricHistoryUseCase
{
    public function __construct(
        private KpiQueryRepository $read,
    ) {
    }

    /**
     * @return Enumerable<int, Kpi>
     */
    public function getHistoryWithMetrics(int $empId): Enumerable
    {
        return $this->read->getHistoryWithMetrics($empId);
    }
}
