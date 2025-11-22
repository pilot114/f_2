<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\DTO\RewardsByNominationResponse;
use App\Domain\Events\Rewards\DTO\RewardsForVerificationFilterResponse;
use App\Domain\Events\Rewards\UseCase\GetRewardsByNominationUseCase;
use App\Domain\Events\Rewards\UseCase\GetRewardsForVerificationFilterUseCase;

class RewardsListController
{
    public function __construct(
        private GetRewardsForVerificationFilterUseCase $getRewardsForVerificationFilterUseCase,
        private GetRewardsByNominationUseCase          $getRewardsByNominationUseCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.getRewardsForVerificationFilter',
        'Список доступных наград для фильтра',
        examples: [
            [
                'summary' => 'получить список доступных наград для фильтра',
                'params'  => [
                    'nominationIds' => [123],
                    'countryId'     => 1,
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.read')]
    public function getRewardsForVerificationFilter(
        #[RpcParam('id номинаций')]
        array $nominationIds,
        #[RpcParam('id страны')]
        int $countryId,
    ): RewardsForVerificationFilterResponse {
        $rewards = $this->getRewardsForVerificationFilterUseCase->getRewardsForVerificationFilter($nominationIds, $countryId);

        return RewardsForVerificationFilterResponse::build($rewards);
    }

    #[RpcMethod(
        'events.rewards.getRewardsByNomination',
        'Список наград по номинации',
        examples: [
            [
                'summary' => 'получить список доступных наград в номинации',
                'params'  => [
                    'rewardId'  => 89828571,
                    'countryId' => 1,
                ],
            ],
        ],
    )]
    /**
     * @return array{
     *     rewardName?: string,
     *     nomination: array{
     *         id: int,
     *         name: string,
     *     },
     *     program: array{
     *          id: int,
     *          name: string,
     *     },
     *     rewards: array<array{
     *         id: int,
     *         name: string,
     *         commentary?: string,
     *         statuses: array<array{
     *             id: int,
     *             name: string,
     *             country: array{
     *                 id: int,
     *                 name: string,
     *             },
     *         }>,
     *     }>
     * }
     */
    #[CpAction('awards_directory.read')]
    public function getRewardsByNomination(
        #[RpcParam('id награды')]
        int $rewardId,
        #[RpcParam('id страны')]
        int $countryId,
    ): array {
        $nomination = $this->getRewardsByNominationUseCase->getNominationWithRewards($rewardId, $countryId);

        return RewardsByNominationResponse::build($nomination, $rewardId);
    }
}
