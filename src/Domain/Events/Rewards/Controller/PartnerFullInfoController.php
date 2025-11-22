<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\DTO\PartnerFullInfoResponse;
use App\Domain\Events\Rewards\UseCase\GetPartnerFullInfoUseCase;

class PartnerFullInfoController
{
    public function __construct(
        private GetPartnerFullInfoUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.getPartnerFullInfo',
        'Получить всю информацию о партнёре',
        examples: [
            [
                'summary' => 'Получить всю информацию о партнёре',
                'params'  => [
                    "partnerId"           => 119858,
                    "programIds"          => [31],
                    "nominationIds"       => [9394335],
                    "rewardIds"           => [25419],
                    "rewardIssuanceState" => 0,
                    "nominationStartDate" => '2024-12-02T00:00:00+00:00',
                    "nominationEndDate"   => '2024-12-02T00:00:00+00:00',
                    "rewardStartDate"     => '2024-12-02T00:00:00+00:00',
                    "rewardEndDate"       => '2024-12-02T00:00:00+00:00',
                    "hideDeletedRewards"  => true,
                ],
            ],
        ],
        isAutomapped: true
    )]

    public function getPartnerFullInfo(PartnerFullInfoRequest $request): PartnerFullInfoResponse
    {
        $partner = $this->useCase->getPartnerFullInfo($request);

        return $partner->toPartnerFullResponse();
    }
}
