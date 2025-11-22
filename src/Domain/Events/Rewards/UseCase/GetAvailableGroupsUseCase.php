<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Repository\GroupQueryRepository;
use Illuminate\Support\Enumerable;

class GetAvailableGroupsUseCase
{
    public function __construct(
        private GroupQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, Group>
     */
    public function getAvailableGroups(): Enumerable
    {
        return $this->repository->getAvailableGroups();
    }
}
