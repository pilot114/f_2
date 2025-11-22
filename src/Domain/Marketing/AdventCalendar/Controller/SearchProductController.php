<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;
use App\Domain\Marketing\AdventCalendar\UseCase\SearchProductOfMonthUseCase;

readonly class SearchProductController
{
    public function __construct(
        private SearchProductOfMonthUseCase $getCountryLanguagesUseCase,
    ) {
    }

    /**
     * @return FindResponse<MonthProduct>
     */
    #[RpcMethod(
        'marketing.adventCalendar.searchProductOfMonth',
        'Поиск продуктов месяца',
        examples: [
            [
                'summary' => 'Поиск продуктов месяца',
                'params'  => [
                    "countryId" => "ru",
                    "q"         => "ново",
                ],
            ],
        ],
    )]
    public function searchProductOfMonth(
        #[RpcParam('Код страны')] string $countryId,
        #[RpcParam('Поиск')] ?string $q,
    ): FindResponse {
        return new FindResponse($this->getCountryLanguagesUseCase->getData($countryId, $q));
    }
}
