<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\CountryListResponse;
use App\Domain\Events\Rewards\UseCase\GetCountryListUseCase;

class CountryListController
{
    public function __construct(
        private GetCountryListUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.getCountryList',
        'Список стран',
    )]
    #[CpAction('awards_directory.read')]
    public function __invoke(): CountryListResponse
    {
        $countryList = $this->useCase->getCountryList();

        return CountryListResponse::build($countryList);
    }
}
