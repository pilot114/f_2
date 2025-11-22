<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\CountryLanguage;
use App\Domain\Marketing\AdventCalendar\Repository\GetCountryLanguagesQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetCountryLanguagesUseCase
{
    public function __construct(
        private GetCountryLanguagesQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, CountryLanguage>
     */
    public function getData(
        string $countryId,
    ): Enumerable {
        return $this->repository->getLanguagesOfCountry($countryId);
    }
}
