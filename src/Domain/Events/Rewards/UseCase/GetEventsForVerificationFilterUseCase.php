<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Event;
use App\Domain\Events\Rewards\Repository\EventQueryRepository;
use Illuminate\Support\Enumerable;

class GetEventsForVerificationFilterUseCase
{
    public function __construct(
        private EventQueryRepository $repository
    ) {
    }

    /**
     * @return Enumerable<int, Event>
     */
    public function getList(): Enumerable
    {
        return $this->repository->getEventsForVerificationFilter();
    }
}
