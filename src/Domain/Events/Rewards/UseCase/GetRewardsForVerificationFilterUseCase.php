<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use Illuminate\Support\Enumerable;

class GetRewardsForVerificationFilterUseCase
{
    public function __construct(
        private RewardQueryRepository $repository
    ) {
    }

    /**
     * @return Enumerable<int, Reward>
     */
    public function getRewardsForVerificationFilter(array $nominationIds, int $countryId): Enumerable
    {
        $rewards = $this->repository->getRewardsForVerificationFilter($nominationIds, $countryId);

        return $rewards->unique(fn (Reward $reward): int => $reward->productId);
    }
}
