<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use Illuminate\Support\Enumerable;

class GetBossesUseCase
{
    public function __construct(
        private KpiQueryRepository $readKpiRepo,
    ) {
    }

    /**
     * @return Enumerable<int, KpiEmployee>
     */
    public function getBosses(
        ?string $q = null
    ): Enumerable {
        return $this->readKpiRepo->getBosses($q);
    }
}
