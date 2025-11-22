<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\HistoryResponse;
use App\Domain\Finance\Kpi\UseCase\GetHistoryUseCase;

class GetHistoryController
{
    public function __construct(
        private GetHistoryUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getHistory',
        'История KPI сотрудника',
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
    public function __invoke(int $empId): HistoryResponse
    {
        [$kpis, $responsible] = $this->useCase->getHistory($empId);
        return HistoryResponse::build($kpis, $empId, $responsible);
    }
}
