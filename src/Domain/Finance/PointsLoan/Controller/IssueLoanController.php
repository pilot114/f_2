<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\PointsLoan\DTO\IssueLoanRequest;
use App\Domain\Finance\PointsLoan\DTO\LoanResponse;
use App\Domain\Finance\PointsLoan\UseCase\GetCurrentPeriodUseCase;
use App\Domain\Finance\PointsLoan\UseCase\IssueLoanUseCase;
use DateTimeImmutable;

class IssueLoanController
{
    public function __construct(
        private IssueLoanUseCase $issueLoanUseCase,
        private GetCurrentPeriodUseCase $getCurrentPeriodUseCase,
    ) {
    }

    #[RpcMethod(
        'finance.pointsLoan.issueLoan',
        'Выдать заём',
        examples: [
            [
                'summary' => 'Выдать заём',
                'params'  => [
                    "partnerId"      => 3889504,
                    "sum"            => 100,
                    "months"         => 5,
                    "monthlyPayment" => 20,
                    "period"         => "2025-08-01T00:00:00+00:00",
                    "guarantor"      => 'CONTRACTNUMBER',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('pam_department/points-loan')]
    public function issueLoan(IssueLoanRequest $request): LoanResponse
    {
        $loan = $this->issueLoanUseCase->issueLoan($request);

        return LoanResponse::build($loan->toArray());
    }

    #[RpcMethod(
        'finance.pointsLoan.getCurrentPeriod',
        'получить текущий период для выдачи займа',
        examples: [
            [
                'summary' => 'получить текущий период для выдачи займа',
                'params'  => [],
            ],
        ],
    )]
    /**
     * @return array{currentPeriod: string}
     */
    public function getCurrentPeriod(): array
    {
        $currentPeriod = $this->getCurrentPeriodUseCase->getCurrentPeriod()->format(DateTimeImmutable::ATOM);
        return [
            'currentPeriod' => $currentPeriod,
        ];
    }
}
