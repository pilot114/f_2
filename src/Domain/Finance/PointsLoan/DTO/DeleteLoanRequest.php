<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Common\Attribute\RpcParam;

class DeleteLoanRequest
{
    public function __construct(
        #[RpcParam('id займа')]
        public readonly int $loanId,
    ) {
    }
}
