<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\UseCase\SendToTreasuryUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class SendToTreasuryController
{
    public function __construct(
        private SendToTreasuryUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.sendToTreasury',
        'Отправка данных по KPI в казначейство',
        examples: [
            [
                'summary' => 'Фильтр по сотруднику',
                'params'  => [
                    'userIds' => [247, 241, 575],
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
        #[RpcParam('Если нужно отправить данные не по всем, а только по выбранным пользователям')]
        array $userIds = [],
    ): bool {
        return $this->useCase->sendToTreasury($this->currentUser, $q, $onlyBoss, $userIds);
    }
}
