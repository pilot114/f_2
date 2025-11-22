<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindItemResponse;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\UseCase\GetMetricTypesUseCase;

class GetMetricTypesController
{
    public function __construct(
        private GetMetricTypesUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<KpiMetricType|FindItemResponse>
     */
    #[RpcMethod(
        'finance.kpi.getMetricTypes',
        'Типы метрик',
        examples: [
            [
                'summary' => 'Поиск по типу метрики',
                'params'  => [
                    'q' => 'проект',
                ],
            ],
        ],
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(
        #[RpcParam('Поиск по имени типа метрики')]
        ?string $q = null,
        #[RpcParam('Расширенная информация (план платежа и диапазоны плана платежа + метрики), если передано true')]
        bool $isExtended = false,
        #[RpcParam('Показывать также выключенные типы метрик')]
        bool $withDisabled = false,
    ): FindResponse {
        $metricTypes = $isExtended
            ? $this->useCase->getMetricTypesExtended($q, $withDisabled)
            : $this->useCase->getMetricTypes($q, $withDisabled)
        ;
        return new FindResponse($metricTypes);
    }
}
