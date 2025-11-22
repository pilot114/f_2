<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use App\Domain\Events\Rewards\DTO\SetRewardTypeRequest;
use App\Domain\Events\Rewards\UseCase\SetRewardTypeUseCase;

class SetRewardTypeController
{
    public function __construct(
        private SetRewardTypeUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.setRewardType',
        'установить тип для награды',
        examples: [
            [
                'summary' => 'установить тип для награды',
                'params'  => [
                    'rewardId' => 123,
                    'typeId'   => 3,
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(SetRewardTypeRequest $request): RewardFullResponse
    {
        $reward = $this->useCase->setRewardType($request);

        return $reward->toRewardFullResponse();
    }
}
