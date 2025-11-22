<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\DTO\BossesResponse;
use App\Domain\Finance\Kpi\UseCase\GetBossesUseCase;

class GetBossesController
{
    public function __construct(
        private GetBossesUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getBosses',
        'Список руководителей для KPI',
    )]
    #[CpAction('accured_kpi.accured_kpi_superboss')]
    public function __invoke(
        #[RpcParam('Поиск по имени пользователя')]
        ?string $q
    ): BossesResponse {
        $entities = $this->useCase->getBosses($q);
        return BossesResponse::build($entities);
    }
}
