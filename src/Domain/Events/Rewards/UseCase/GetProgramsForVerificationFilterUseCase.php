<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use Illuminate\Support\Enumerable;

class GetProgramsForVerificationFilterUseCase
{
    public function __construct(
        private ProgramQueryRepository $repository
    ) {
    }

    /**
     * @return Enumerable<int, Program>
     */
    public function getList(): Enumerable
    {
        return $this->repository->getProgramsForVerificationFilter();
    }
}
