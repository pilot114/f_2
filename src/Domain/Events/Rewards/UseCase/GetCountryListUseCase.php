<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Repository\CountryQueryRepository;
use Illuminate\Support\Enumerable;

class GetCountryListUseCase
{
    public function __construct(
        private CountryQueryRepository $readCountry,
    ) {
    }

    /**
     * @return Enumerable<int, Country>
     */
    public function getCountryList(): Enumerable
    {
        return $this->readCountry->findAll();
    }
}
