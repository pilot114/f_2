<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Repository\NominationQueryRepository;
use Illuminate\Support\Enumerable;

class GetNominationsForVerificationFilterUseCase
{
    public function __construct(
        private NominationQueryRepository $repository
    ) {
    }

    /** @return Enumerable<int, Nomination> */
    public function getNominationsForVerificationFilter(array $programIds): Enumerable
    {
        return $this->repository->getNominationsForVerificationFilter($programIds);
    }
}
