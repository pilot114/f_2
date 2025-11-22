<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\KpiResponse;
use App\Domain\Finance\Kpi\DTO\UpdateKpiRequest;
use App\Domain\Finance\Kpi\UseCase\MultiUpdateUseCase;

class MultiUpdateController
{
    public function __construct(
        private MultiUpdateUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.multiUpdateHistory',
        'Массовое редактирование записей KPI',
        examples: [
            [
                'summary' => 'Данные для редактирования',
                'params'  => [
                    'kpi' => '[{
                        "id": 10110,
                        "value": 100,
                        "valueCalculated": 100,
                        "empId": 4026,
                        "metrics": [
                            {
                                "id": 3,
                                "factual": 11,
                                "plan": 12,
                                "weight": 0.7
                            }
                        ]
                    }, {
                        "id": 10,
                        "value": 31,
                        "empId": 4026
                    }]',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_departmentboss')]
    /**
     * @param array<UpdateKpiRequest> $kpi
     * @return array<KpiResponse>
     */
    public function __invoke(array $kpi): array
    {
        $updatedKpi = $this->useCase->multipleUpdateKpi($kpi);
        $responses = [];
        foreach ($updatedKpi as $i => $item) {
            $responses[] = KpiResponse::build($item, $kpi[$i]->empId);
        }
        return $responses;
    }
}
