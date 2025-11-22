<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use DateTimeInterface;
use Illuminate\Support\Enumerable;

class GetListUseCase
{
    public function __construct(
        private KpiQueryRepository $readKpiRepo,
    ) {
    }

    /**
     * @return array{0: (Enumerable<int, KpiDepartment>), 1: ?DateTimeInterface}
     */
    public function getList(
        int $empId,
        ?string $q = null,
        bool $onlyBoss = false,
    ): array {
        $lastDateSend = $this->readKpiRepo->lastDateSend($empId);
        $collection = $this->readKpiRepo->getList($empId, $q, $onlyBoss);
        return [$collection, $lastDateSend];
    }
}
