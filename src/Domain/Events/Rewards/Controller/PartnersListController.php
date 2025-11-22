<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\DTO\PartnersByContractsRequest;
use App\Domain\Events\Rewards\DTO\PartnersByContractsResponse;
use App\Domain\Events\Rewards\DTO\PartnersByEventRequest;
use App\Domain\Events\Rewards\DTO\PartnersByEventResponse;
use App\Domain\Events\Rewards\UseCase\GetPartnersByContractsUseCase;
use App\Domain\Events\Rewards\UseCase\GetPartnersByEventUseCase;

class PartnersListController
{
    public function __construct(
        private GetPartnersByEventUseCase     $getPartnersByEventUseCase,
        private GetPartnersByContractsUseCase $getPartnersByContractsUseCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.getPartnersByEvent',
        'Список партнёров с наградами зарегистрированных на мероприятие',
        examples: [
            [
                'summary' => 'получить список партнеров с наградами зарегистрированных на мероприятие',
                'params'  => [
                    "eventId"             => 119858,
                    "partnerStatus"       => 1,
                    "country"             => "Q_ANY",
                    "hasPenalty"          => false,
                    "programIds"          => [31],
                    "nominationIds"       => [9394335],
                    "rewardIds"           => [25419],
                    "nominationStartDate" => "2024-12-02T00:00:00+00:00",
                    "nominationEndDate"   => "2024-12-02T00:00:00+00:00",
                    "page"                => 1,
                    "perPage"             => 50,
                    "search"              => 'поиск по контракту или фио',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('awards_directory.read')]
    public function getPartnersByEvent(
        PartnersByEventRequest $request
    ): PartnersByEventResponse {
        ini_set('memory_limit', -1);
        $partners = $this->getPartnersByEventUseCase->getList($request);
        $total = $this->getPartnersByEventUseCase->count($request);

        return PartnersByEventResponse::build(
            $partners,
            $total,
            $request->page,
            $request->perPage
        );
    }

    #[RpcMethod(
        'events.rewards.getPartnersByContracts',
        'Список партнеров по номерам контрактов',
        examples: [
            [
                'summary' => 'получить список партнеров зарегистрированных на мероприятие',
                'params'  => [
                    "contracts"           => ['119858', '12341414'],
                    "country"             => "Q_ANY",
                    "programIds"          => [23],
                    "nominationIds"       => [9394335],
                    "rewardIds"           => [25419],
                    "rewardIssuanceState" => 0,
                    "nominationStartDate" => '2024-12-02T00:00:00+00:00',
                    "nominationEndDate"   => '2024-12-02T00:00:00+00:00',
                    "rewardStartDate"     => '2024-12-02T00:00:00+00:00',
                    "rewardEndDate"       => '2024-12-02T00:00:00+00:00',
                    'hideDeletedRewards'  => true,
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('awards_directory.read')]
    public function getPartnersByContracts(
        PartnersByContractsRequest $request
    ): PartnersByContractsResponse {
        ini_set('memory_limit', -1);

        $partnersWithActiveReward = $this->getPartnersByContractsUseCase->getWithActiveRewards($request);
        $partnersWithDeletedReward = $request->hideDeletedRewards ? EnumerableWithTotal::build() : $this->getPartnersByContractsUseCase->getWithDeletedRewards($request);

        return PartnersByContractsResponse::build($partnersWithActiveReward, $partnersWithDeletedReward);
    }
}
