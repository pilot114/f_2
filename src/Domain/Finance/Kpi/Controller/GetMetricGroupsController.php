<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\DTO\MetricGroupsResponse;
use App\Domain\Finance\Kpi\UseCase\GetMetricGroupsUseCase;

class GetMetricGroupsController
{
    public function __construct(
        private GetMetricGroupsUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getMetricGroups',
        'получение списка групп метрик',
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(
    ): MetricGroupsResponse {
        $groups = $this->useCase->getMetricGroups();
        return MetricGroupsResponse::build($groups);
    }
}
