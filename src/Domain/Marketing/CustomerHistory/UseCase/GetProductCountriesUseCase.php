<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\ProductCountry;
use App\Domain\Marketing\CustomerHistory\Repository\ProductCountryQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetProductCountriesUseCase
{
    public function __construct(
        private ProductCountryQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, ProductCountry>
     */
    public function getProductCountries(string $lang): Enumerable
    {
        return $this->repository->getProductCountries($lang);
    }
}
