<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;

class GetResponsibleUseCase
{
    public function __construct(
        private KpiResponsibleQueryRepository $readRepo,
    ) {
    }

    public function get(int $id): KpiResponsible
    {
        return $this->readRepo->getResponsible($id);
    }
}
