<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\PointsLoan\DTO\EditLoanRequest;
use App\Domain\Finance\PointsLoan\DTO\LoanResponse;
use App\Domain\Finance\PointsLoan\UseCase\EditLoanUseCase;

class EditLoanController
{
    public function __construct(
        private EditLoanUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'finance.pointsLoan.editLoan',
        'Редактировать заём',
        examples: [
            [
                'summary' => 'Редактировать заём',
                'params'  => [
                    'loanId'         => 4,
                    'sum'            => 100,
                    'months'         => 3,
                    'monthlyPayment' => 33.33,
                    'guarantor'      => 'CONTRACTNUMBER',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('pam_department/points-loan')]
    public function edit(EditLoanRequest $request): LoanResponse
    {
        $loan = $this->useCase->edit($request);

        return LoanResponse::build($loan->toArray());
    }
}
