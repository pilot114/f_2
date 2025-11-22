<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\HistoryMetricKpiResponse;
use App\Domain\Finance\Kpi\UseCase\GetMetricHistoryUseCase;

class GetMetricHistoryController
{
    public function __construct(
        private GetMetricHistoryUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getMetricHistory',
        'История KPI-метрик сотрудника',
        examples: [
            [
                'summary' => 'Фильтр по сотруднику',
                'params'  => [
                    'empId' => 4026,
                ],
            ],
        ],
    )]
    #[CpAction('accured_kpi.accured_kpi_departmentboss')]
    public function __invoke(int $empId): HistoryMetricKpiResponse
    {
        $entities = $this->useCase->getHistoryWithMetrics($empId);
        return HistoryMetricKpiResponse::build($entities, $empId);
    }
}
