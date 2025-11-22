<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\CreateKpiMetricRequest;
use App\Domain\Finance\Kpi\DTO\MetricResponse;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricRequest;
use App\Domain\Finance\Kpi\UseCase\WriteMetricUseCase;

class WriteMetricController
{
    public function __construct(
        private WriteMetricUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.createMetric',
        'создание метрики',
        examples: [
            [
                'summary' => 'создание метрики',
                'params'  => [
                    'metric' => '{
                        "name": "test",
                        "kpiType": 1,
                        "calculationType": 1,
                        "calculationTypeDescription": "test",
                        "unitType": 1,
                        "groupId": 1,   
                        "metricTypeId": 1,
                        "metricDepartments": [
                            {"departmentId": 1, "postId": 42},
                            {"departmentId": 1, "postId": 43}
                        ]
                    }',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function create(
        CreateKpiMetricRequest $metric
    ): MetricResponse {
        $createdMetric = $this->useCase->createMetric($metric);
        return new MetricResponse(...$createdMetric->toArray());
    }

    #[RpcMethod(
        'finance.kpi.updateMetric',
        'редактирование метрики',
        examples: [
            [
                'summary' => 'редактирование метрики',
                'params'  => [
                    'metric' => '{
                        "id": 27,
                        "name": "test",
                        "kpiType": 1,
                        "calculationType": 1,
                        "calculationTypeDescription": "test",
                        "unitType": 1,
                        "groupId": 1,   
                        "metricTypeId": 1,
                        "isActive": false,
                        "metricDepartments": [
                            {"departmentId": 1, "postId": 42},
                            {"departmentId": 1, "postId": 43}
                        ]
                    }',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function update(
        UpdateKpiMetricRequest $metric
    ): MetricResponse {
        $updatedMetric = $this->useCase->updateMetric($metric);
        return new MetricResponse(...$updatedMetric->toArray());
    }
}
