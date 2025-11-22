<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\DTO\ListResponse;
use App\Domain\Finance\Kpi\UseCase\GetListUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;

class GetListController
{
    public function __construct(
        private GetListUseCase          $useCase,
        private SecurityUser            $currentUser,
        private SecurityQueryRepository $secRepo,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getList',
        'Список KPI для редактирования',
        examples: [
            [
                'summary' => 'Фильтр по имени пользователя',
                'params'  => [
                    'q'        => 'Иванов',
                    'onlyBoss' => false,
                ],
            ],
        ],
    )]
    #[CpAction('accured_kpi.accured_kpi_departmentboss')]
    public function __invoke(
        #[RpcParam('Поиск по имени пользователя')]
        ?string $q,
        #[RpcParam('Показывать только руководителей')]
        bool $onlyBoss = false,
    ): ListResponse {
        [$entities, $lastDateSend] = $this->useCase->getList($this->currentUser->id, $q, $onlyBoss);
        $currentUserIsDepartmentBoss = $this->secRepo->isDepartmentBoss($this->currentUser->id);
        return ListResponse::build($entities, $lastDateSend, $currentUserIsDepartmentBoss);
    }
}
