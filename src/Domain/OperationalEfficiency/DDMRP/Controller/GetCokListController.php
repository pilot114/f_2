<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\CokResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\GetCokListRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetCokListUseCase;

class GetCokListController
{
    public function __construct(
        private GetCokListUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<CokResponse>
     */
    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.getCokList',
        summary: 'Получить данные по партнёру',
        examples: [
            [
                'summary' => 'получить список цоков',
                'params'  => [
                    'countryId'        => 1,
                    'regionDirectorId' => "Q_ANY",
                    'search'           => 'C30060',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('ddmrp_admin')]
    public function __invoke(GetCokListRequest $request): FindResponse
    {
        $cokList = $this->useCase
            ->getCokList($request)
            ->map(fn (Cok $item): CokResponse => $item->toCokResponse());

        return new FindResponse($cokList);
    }
}
