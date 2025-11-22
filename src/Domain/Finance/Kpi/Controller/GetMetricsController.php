<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindItemResponse;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\UseCase\GetMetricsUseCase;

class GetMetricsController
{
    public function __construct(
        private GetMetricsUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<FindItemResponse|KpiMetric>
     */
    #[RpcMethod(
        'finance.kpi.getMetrics',
        'получение списка метрик',
        examples: [
            [
                'summary' => 'Применение фильтров',
                'params'  => [
                    'groupId'      => 1,
                    'metricTypeId' => 2,
                    'q'            => 'super',
                ],
            ],
        ]
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(
        #[RpcParam('отдел / группа. При isExtended = false игнорируется')]
        ?int $groupId = null,
        #[RpcParam('тип метрики. При isExtended = false игнорируется')]
        ?int $metricTypeId = null,
        #[RpcParam('поиск по названию')]
        ?string $q = null,
        #[RpcParam('Расширенная информация, если передано true')]
        bool $isExtended = false,
        #[RpcParam('Показывать также выключенные метрики')]
        bool $withDisabled = false,
    ): FindResponse {
        if ($isExtended) {
            $metrics = $this->useCase->getMetricsExtended($groupId, $metricTypeId, $q, $withDisabled);
        } else {
            $metrics = $this->useCase->getMetrics($q, $withDisabled);
        }
        return new FindResponse($metrics);
    }
}
