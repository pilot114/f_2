<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\PointsLoan\DTO\DeleteLoanRequest;
use App\Domain\Finance\PointsLoan\UseCase\DeleteLoanUseCase;

class DeleteLoanController
{
    public function __construct(
        private DeleteLoanUseCase $deleteLoanUseCase
    ) {
    }

    #[RpcMethod(
        'finance.pointsLoan.deleteLoan',
        'Удалить заём',
        examples: [
            [
                'summary' => 'Удалить заём',
                'params'  => [
                    'loanId' => 123,
                ],
            ],
        ],
        isAutomapped: true,
    )]
    #[CpMenu('pam_department/points-loan')]
    public function delete(DeleteLoanRequest $request): bool
    {
        return $this->deleteLoanUseCase->delete($request);
    }
}
