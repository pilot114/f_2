<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\UseCase\AutoCompleteUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class AutoCompleteController
{
    public function __construct(
        private AutoCompleteUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.autoComplete',
        'Автоматическое выставление KPI',
    )]
    #[CpAction('accured_kpi.accured_kpi_departmentboss')]
    public function __invoke(
        #[RpcParam('Поиск по имени пользователя')]
        ?string $q,
        #[RpcParam('Показывать только руководителей')]
        bool $onlyBoss = false,
    ): bool {
        return $this->useCase->autoComplete($this->currentUser->id, $q, $onlyBoss);
    }
}
