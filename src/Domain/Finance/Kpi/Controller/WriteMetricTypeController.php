<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\CreateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\DTO\MetricTypeResponse;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\UseCase\WriteMetricTypeUseCase;

class WriteMetricTypeController
{
    public function __construct(
        private WriteMetricTypeUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.createMetricType',
        'создание типа метрики',
        examples: [
            [
                'summary' => 'Пример создания типа метрики с диапазонами',
                'params'  => [
                    'metricType' => '{
                        "name": "new metric type",
                        "planType": "RANGES",
                        "ranges": [
                            {"startPercent": 0, "endPercent": 50, "kpiPercent": 80},
                            {"startPercent": 51, "endPercent": 100, "kpiPercent": 100}
                        ]
                    }',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function create(
        CreateKpiMetricTypeRequest $metricType,
    ): MetricTypeResponse {
        $createdMetricType = $this->useCase->createMetricType($metricType);
        return new MetricTypeResponse(...$createdMetricType->toArray());
    }

    #[RpcMethod(
        'finance.kpi.updateMetricType',
        'редактирование типа метрики',
        examples: [
            [
                'summary' => 'Пример редактирование типа метрики с диапазонами. Новые диапазоны заменяют старые',
                'params'  => [
                    'metricType' => '{
                        "id": 42,
                        "name": "new metric type",
                        "planType": "RANGES",
                        "ranges": [
                            {"startPercent": 0, "endPercent": 50, "kpiPercent": 80},
                            {"startPercent": 51, "endPercent": 100, "kpiPercent": 100}
                        ]
                    }',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function update(
        UpdateKpiMetricTypeRequest $metricType,
    ): MetricTypeResponse {
        $updatedMetricType = $this->useCase->updateMetricType($metricType);
        return new MetricTypeResponse(...$updatedMetricType->toArray());
    }
}
