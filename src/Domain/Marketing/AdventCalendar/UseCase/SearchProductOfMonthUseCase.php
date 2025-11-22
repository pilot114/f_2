<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Common\Exception\ProductImageException;
use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;
use App\Domain\Marketing\AdventCalendar\Repository\GetAdventProductQueryRepository;
use Illuminate\Support\Enumerable;

readonly class SearchProductOfMonthUseCase
{
    public function __construct(
        private GetAdventProductQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, MonthProduct>
     * @throws ProductImageException
     */
    public function getData(
        string $countryId,
        ?string $q,
    ): Enumerable {
        return $this->repository->getData($countryId, $q);
    }
}
