<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use Illuminate\Support\Enumerable;

class GetPartnerLoanHistoryUseCase
{
    public function __construct(
        private LoanQueryRepository $queryRepository
    ) {
    }

    /** @return Enumerable<int, Loan> */
    public function getHistory(int $partnerId): Enumerable
    {
        return $this->queryRepository->getHistory($partnerId);
    }
}
