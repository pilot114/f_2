<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Common\Attribute\RpcParam;
use Symfony\Component\Validator\Constraints as Assert;

class EditLoanRequest
{
    public function __construct(
        #[RpcParam('id займа')]
        public readonly int $loanId,

        #[Assert\GreaterThan(0)]
        #[RpcParam('сумма займа')]
        public readonly float $sum,

        #[Assert\GreaterThanOrEqual(1)]
        #[RpcParam('Количество месяцев')]
        public readonly int $months,

        #[Assert\GreaterThan(0)]
        #[RpcParam('Ежемесячный платёж)')]
        public readonly float $monthlyPayment,

        #[RpcParam('Контракт партнера-гаранта')]
        public readonly ?string $guarantor = null,
    ) {
    }
}
