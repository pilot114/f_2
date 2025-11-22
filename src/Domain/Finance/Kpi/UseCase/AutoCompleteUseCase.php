<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;

class AutoCompleteUseCase
{
    public function __construct(
        private KpiQueryRepository $read,
        private KpiCommandRepository $write,
    ) {
    }

    public function autoComplete(int $empId, ?string $q, bool $onlyBoss = false): bool
    {
        $finEmpIds = $this->read->findEmpForExport($empId, $q, $onlyBoss);

        return $this->write->autoComplete($finEmpIds);
    }
}
