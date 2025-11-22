<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Country;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CountryQueryRepository;
use Illuminate\Support\Enumerable;

class GetCountryListUseCase
{
    public function __construct(
        private CountryQueryRepository $queryRepository
    ) {
    }

    /** @return Enumerable<int, Country> */
    public function getCountyList(): Enumerable
    {
        return $this->queryRepository->getCountryList();
    }
}
