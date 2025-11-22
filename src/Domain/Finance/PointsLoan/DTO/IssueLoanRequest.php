<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Common\Attribute\RpcParam;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class IssueLoanRequest
{
    public function __construct(
        #[RpcParam('id партнера')]
        public readonly int               $partnerId,
        #[RpcParam('сумма займа')]
        #[Assert\GreaterThan(0)]
        public readonly float             $sum,
        #[RpcParam('Количество месяцев(срок займа)')]
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int               $months,
        #[RpcParam('Месячный платёж')]
        #[Assert\GreaterThan(0)]
        public readonly float             $monthlyPayment,
        #[RpcParam('Период в котором выдаётся заём')]
        public readonly DateTimeImmutable $period,
        #[RpcParam('Контракт партнера-гаранта')]
        public readonly ?string           $guarantor = null,
    ) {
    }
}
