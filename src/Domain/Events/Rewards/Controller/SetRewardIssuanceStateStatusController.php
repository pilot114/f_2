<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\SetRewardIssuanceStateStatusRequest;
use App\Domain\Events\Rewards\UseCase\SetRewardIssuanceStateStatusUseCase;

class SetRewardIssuanceStateStatusController
{
    public function __construct(
        private SetRewardIssuanceStateStatusUseCase $setRewardIssuanceStateStatusUseCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.setRewardIssuanceStateStatus',
        'Изменить статус состояния выдачи награды в режиме по контракту',
        examples: [
            [
                'summary' => 'Изменить статус состояния выдачи награды в режиме по контракту',
                'params'  => [
                    'partnerId'            => 1234,
                    'rewardIssuanceStates' => [
                        [
                            'id'      => 1233,
                            'status'  => 3,
                            'comment' => 'Комментарий',
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        SetRewardIssuanceStateStatusRequest $request,
    ): void {
        $this->setRewardIssuanceStateStatusUseCase->setRewardIssuanceStateStatus($request);
    }
}
