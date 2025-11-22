<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Repository\CpDepartmentQueryRepository;
use Illuminate\Support\Enumerable;

class GetKpiDepartmentsUseCase
{
    public function __construct(
        private CpDepartmentQueryRepository $repo,
    ) {
    }

    /**
     * @return Enumerable<int, CpDepartment>
     */
    public function getDepartments(): Enumerable
    {
        return $this->repo->getDepartments();
    }
}
