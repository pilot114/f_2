<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\DTO\DeputyResponse;
use App\Domain\Finance\Kpi\UseCase\GetDeputyListUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class GetDeputyListController
{
    public function __construct(
        private GetDeputyListUseCase $useCase,
        private SecurityUser         $currentUser,
    ) {
    }

    /**
     * @return FindResponse<DeputyResponse>
     */
    #[RpcMethod(
        'finance.kpi.getDeputyList',
        'Список заместителей текущего пользователя',
    )]
    public function __invoke(): FindResponse
    {
        $list = $this->useCase->getList($this->currentUser->id);

        $items = [];
        foreach ($list as $item) {
            $items[] = new DeputyResponse(...$item->toArray());
        }
        return new FindResponse($items);
    }
}
