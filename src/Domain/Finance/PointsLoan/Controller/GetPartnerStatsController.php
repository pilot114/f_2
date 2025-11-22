<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\PointsLoan\DTO\GetPartnerStatsResponse;
use App\Domain\Finance\PointsLoan\UseCase\GetPartnerStatsUseCase;

class GetPartnerStatsController
{
    public function __construct(
        private GetPartnerStatsUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'finance.pointsLoan.getPartnerStats',
        'Информация о партнере и его показателях',
        examples: [
            [
                'summary' => 'Информация о партнере и его показателях',
                'params'  => [
                    'contract' => '"55"',
                ],
            ],
        ],
    )]
    #[CpMenu('pam_department/points-loan')]
    public function __invoke(
        #[RpcParam('контракт')]
        string $contract
    ): GetPartnerStatsResponse {
        $partner = $this->useCase->getPartnerStats($contract);

        return GetPartnerStatsResponse::build($partner);
    }
}
