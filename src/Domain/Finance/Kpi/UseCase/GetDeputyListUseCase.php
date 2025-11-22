<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use Illuminate\Support\Enumerable;

class GetDeputyListUseCase
{
    public function __construct(
        private KpiQueryRepository $readKpiRepo,
    ) {
    }

    /**
     * @return Enumerable<int, Deputy>
     */
    public function getList(int $currentUserId): Enumerable
    {
        return $this->readKpiRepo->getDeputyList($currentUserId);
    }
}
