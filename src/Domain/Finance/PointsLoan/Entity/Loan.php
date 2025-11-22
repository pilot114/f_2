<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use App\Domain\Finance\PointsLoan\Enum\LoanStatus;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'net.employee_point_credits', sequenceName: 'net.employee_point_credits_sq')]
class Loan
{
    public function __construct(
        #[Column(name: 'id')] private int                               $id,
        #[Column(name: 'newspis_id')] private int                       $accrualOperationId,
        #[Column(name: 'employee_id')] public readonly int              $partnerId,
        #[Column(name: 'start_date')] public readonly DateTimeImmutable $startDate,
        #[Column(name: 'summ')] private float                           $sum,
        #[Column(name: 'months')] private int                           $months,
        #[Column(name: 'month_payment')] private float                  $monthlyPayment,
        #[Column(name: 'end_date')] public readonly ?DateTimeImmutable  $endDate = null,
        #[Column(name: 'link_id')] public readonly ?int                 $linkedLoanId = null,
        #[Column(name: 'paid_summ')] public readonly float              $totalPaid = 0,
        #[Column(name: 'guarantor')] private ?Guarantor                 $guarantor = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isStartDateInCurrentPeriod(DateTimeImmutable $currentPeriod): bool
    {
        return $currentPeriod->format('d.m.Y') === $this->startDate->format('d.m.Y');
    }

    public function isMonthlyPaymentValid(float $monthlyPayment): bool
    {
        if ($this->months <= 0) {
            return false;
        }

        return $monthlyPayment > 0 && $monthlyPayment <= $this->sum;
    }

    public function update(float $sum, int $months, float $monthlyPayment, ?Guarantor $guarantor): void
    {
        $this->sum = $sum;
        $this->months = $months;
        $this->monthlyPayment = $monthlyPayment;
        $this->guarantor = $guarantor;
    }

    public function getAccrualOperationId(): int
    {
        return $this->accrualOperationId;
    }

    public function setAccrualOperationId(int $accrualOperationId): void
    {
        $this->accrualOperationId = $accrualOperationId;
    }

    public function getStatus(): LoanStatus
    {
        return $this->endDate instanceof DateTimeImmutable ? LoanStatus::PAID : LoanStatus::NOT_PAID;
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'accrualOperationId' => $this->accrualOperationId,
            'partnerId'          => $this->partnerId,
            'startDate'          => $this->startDate->format('Y-m-d'),
            'sum'                => $this->sum,
            'months'             => $this->months,
            'monthlyPayment'     => $this->monthlyPayment,
            'guarantorContract'  => $this->guarantor?->contract,
            'endDate'            => $this->endDate?->format('Y-m-d'),
            'linkedLoanId'       => $this->linkedLoanId,
            'totalPaid'          => $this->totalPaid,
            'loanStatus'         => $this->getStatus(),
        ];
    }
}
