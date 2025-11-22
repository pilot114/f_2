<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Events\Rewards\DTO\RewardTypeResponse;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\UseCase\GetAvailableRewardTypesUseCase;

class RewardTypesListController
{
    public function __construct(
        private GetAvailableRewardTypesUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<RewardTypeResponse>
     */
    #[RpcMethod(
        'events.rewards.getAvailableRewardTypes',
        'Список доступных типов наград',
        examples: [
            [
                'summary' => 'Список доступных типов наград',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(): FindResponse
    {
        $items = $this->useCase->getAll()
            ->map(fn (RewardType $rewardType): RewardTypeResponse => $rewardType->toRewardTypeResponse());

        return new FindResponse($items);
    }
}
