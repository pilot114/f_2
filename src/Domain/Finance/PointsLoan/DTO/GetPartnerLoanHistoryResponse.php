<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Domain\Finance\PointsLoan\Entity\Loan;
use Illuminate\Support\Enumerable;

class GetPartnerLoanHistoryResponse
{
    private function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }

    /** @param Enumerable<int, Loan> $loansHistory */
    public static function build(Enumerable $loansHistory): self
    {
        $items = $loansHistory
            ->map(fn (Loan $loan): array => [
                ...$loan->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $loansHistory->getTotal()
        );
    }
}
