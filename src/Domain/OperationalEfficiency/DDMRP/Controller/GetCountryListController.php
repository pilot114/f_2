<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\CountryResponse;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Country;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetCountryListUseCase;

class GetCountryListController
{
    public function __construct(
        private GetCountryListUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<CountryResponse>
     */
    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.getCountryList',
        summary: 'получить список стран для фильтра',
        examples: [
            [
                'summary' => 'получить список стран для фильтра',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(): FindResponse
    {
        $countryResponseList = $this->useCase
            ->getCountyList()
            ->map(fn (Country $item): CountryResponse => $item->toCountryResponse());

        return new FindResponse($countryResponseList);
    }
}
