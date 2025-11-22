<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\PointsLoan\DTO\GetPartnerLoanHistoryResponse;
use App\Domain\Finance\PointsLoan\UseCase\GetPartnerLoanHistoryUseCase;

class GetPartnerLoanHistoryController
{
    public function __construct(
        private GetPartnerLoanHistoryUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'finance.pointsLoan.getPartnerLoanHistory',
        'История займов партнера',
        examples: [
            [
                'summary' => 'История займов партнера',
                'params'  => [
                    'partnerId' => 3889504,
                ],
            ],
        ],
    )]
    #[CpMenu('pam_department/points-loan')]
    public function __invoke(
        #[RpcParam('id партнера')]
        int $partnerId
    ): GetPartnerLoanHistoryResponse {
        $loans = $this->useCase->getHistory($partnerId);

        return GetPartnerLoanHistoryResponse::build($loans);
    }
}
