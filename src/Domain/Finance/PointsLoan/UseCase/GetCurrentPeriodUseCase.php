<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use DateTimeImmutable;

class GetCurrentPeriodUseCase
{
    public function __construct(
        private LoanQueryRepository $repository
    ) {
    }

    public function getCurrentPeriod(): DateTimeImmutable
    {
        return $this->repository->getCurrentPeriod();
    }
}
