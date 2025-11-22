<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\RewardType;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Enumerable;

class GetAvailableRewardTypesUseCase
{
    public function __construct(
        /** @var QueryRepositoryInterface<RewardType> */
        private QueryRepositoryInterface $readRewardTypes,
    ) {
    }

    /** @return Enumerable<int, RewardType> */
    public function getAll(): Enumerable
    {
        return $this->readRewardTypes->findAll();
    }
}
