<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\RewardTypeRequest;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Repository\GroupQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetGroupsUseCase
{
    public function __construct(
        private GroupQueryRepository $repository,
    ) {
    }

    /**
     * @param RewardTypeRequest[] $rewardTypes
     * @return Enumerable<int, Group>
     */
    public function getGroups(int|FilterOption $country, ?string $search, bool $status, array $rewardTypes): Enumerable
    {
        return $this->repository->getGroups($country, $search, $status, $rewardTypes);
    }
}
