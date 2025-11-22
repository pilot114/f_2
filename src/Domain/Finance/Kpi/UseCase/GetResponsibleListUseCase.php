<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use Illuminate\Support\Enumerable;

class GetResponsibleListUseCase
{
    public function __construct(
        private KpiResponsibleQueryRepository $readRepo,
    ) {
    }

    /**
     * @return Enumerable<int, KpiResponsible>
     */
    public function getList(): Enumerable
    {
        return $this->readRepo->getResponsibles();
    }
}
