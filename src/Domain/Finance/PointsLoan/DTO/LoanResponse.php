<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Domain\Finance\PointsLoan\Enum\LoanStatus;

class LoanResponse
{
    private function __construct(
        public readonly int $id,
        public readonly int $accrualOperationId,
        public readonly int $partnerId,
        public readonly string $startDate,
        public readonly float $sum,
        public readonly int $months,
        public readonly float $monthlyPayment,
        public readonly ?string $guarantorContract,
        public readonly ?string $endDate,
        public readonly ?int $linkedLoanId,
        public readonly float $totalPaid,
        public readonly LoanStatus $loanStatus,
    ) {
    }

    public static function build(array $data): self
    {
        return new self(
            id: $data['id'],
            accrualOperationId: $data['accrualOperationId'],
            partnerId: $data['partnerId'],
            startDate: $data['startDate'],
            sum: $data['sum'],
            months: $data['months'],
            monthlyPayment: $data['monthlyPayment'],
            guarantorContract: $data['guarantorContract'],
            endDate: $data['endDate'],
            linkedLoanId: $data['linkedLoanId'],
            totalPaid: $data['totalPaid'],
            loanStatus: $data['loanStatus'],
        );
    }
}
