<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\DepartmentsResponse;
use App\Domain\Finance\Kpi\UseCase\GetKpiDepartmentsUseCase;

class GetDepartmentsController
{
    public function __construct(
        private GetKpiDepartmentsUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getDepartments',
        'получение списка департаментов для KPI',
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(): DepartmentsResponse
    {
        $departs = $this->useCase->getDepartments();
        return DepartmentsResponse::build($departs);
    }
}
