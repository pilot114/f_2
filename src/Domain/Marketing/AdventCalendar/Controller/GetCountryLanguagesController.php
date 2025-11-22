<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\AdventCalendar\Entity\CountryLanguage;
use App\Domain\Marketing\AdventCalendar\UseCase\GetCountryLanguagesUseCase;

readonly class GetCountryLanguagesController
{
    public function __construct(
        private GetCountryLanguagesUseCase $getCountryLanguagesUseCase,
    ) {
    }

    /**
     * @return FindResponse<CountryLanguage>
     */
    #[RpcMethod(
        'marketing.adventCalendar.getCountryLanguages',
        'Получение языков по странам',
        examples: [
            [
                'summary' => 'Получение языков по странам',
                'params'  => [
                    "countryId" => "ru",
                ],
            ],
        ],
    )]
    public function getCountryLanguages(
        #[RpcParam('Код страны')] string $countryId,
    ): FindResponse {
        return new FindResponse($this->getCountryLanguagesUseCase->getData($countryId));
    }
}
