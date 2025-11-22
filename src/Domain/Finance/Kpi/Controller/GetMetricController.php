<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\DTO\MetricResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\UseCase\GetMetricUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMetricController
{
    public function __construct(
        private GetMetricUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getMetric',
        'получение метрики по id',
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
        #[RpcParam('id метрики')]
        int $id,
    ): MetricResponse {
        $metric = $this->useCase->getMetric($id);
        if (!$metric instanceof KpiMetric) {
            throw new NotFoundHttpException("Не найдена метрика с id: $id");
        }
        return new MetricResponse(...$metric->toArray());
    }
}
