<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\DTO\KpiResponsibleResponse;
use App\Domain\Finance\Kpi\UseCase\GetResponsibleListUseCase;

class GetResponsibleListController
{
    public function __construct(
        private GetResponsibleListUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<KpiResponsibleResponse>
     */
    #[RpcMethod(
        'finance.kpi.getResponsibleList',
        'Список ответственных за получение файлов по KPI',
    )]
    public function __invoke(): FindResponse
    {
        $list = $this->useCase->getList();
        $items = [];
        foreach ($list as $item) {
            $items[] = new KpiResponsibleResponse(...$item->toArray());
        }

        return new FindResponse($items);
    }
}
