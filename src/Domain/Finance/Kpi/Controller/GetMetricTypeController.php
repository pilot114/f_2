<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\DTO\MetricTypeResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\UseCase\GetMetricTypeUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMetricTypeController
{
    public function __construct(
        private GetMetricTypeUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getMetricType',
        'получение типа метрики по id',
        examples: [
            [
                'summary' => 'получение метрики по id',
                'params'  => [
                    'id' => 1,
                ],
            ],
        ],
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(
        #[RpcParam('id типа метрики')]
        int $id,
    ): MetricTypeResponse {
        $metricType = $this->useCase->getMetricType($id);
        if (!$metricType instanceof KpiMetricType) {
            throw new NotFoundHttpException("Не найден тип метрики с id $id");
        }
        return new MetricTypeResponse(...$metricType->toArray());
    }
}
