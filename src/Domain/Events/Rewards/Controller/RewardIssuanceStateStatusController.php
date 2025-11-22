<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateStatusTypeResponse;
use App\Domain\Events\Rewards\UseCase\GetAvailableRewardIssuanceStateStatusesUseCase;

class RewardIssuanceStateStatusController
{
    public function __construct(
        private GetAvailableRewardIssuanceStateStatusesUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.getAvailableRewardIssuanceStateStatuses',
        'Список доступных статусов состояний выдачи наград',
        examples: [
            [
                'summary' => 'Список доступных статусов состояний выдачи наград',
                'params'  => [
                ],
            ],
        ],
    )]

    /** @return RewardIssuanceStateStatusTypeResponse[] */
    public function getAvailableRewardIssuanceStateStatuses(): array
    {
        return $this->useCase->getList()->toArray();
    }
}
