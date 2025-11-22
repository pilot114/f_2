<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\CustomerHistory\Entity\ProductCountry;
use App\Domain\Marketing\CustomerHistory\UseCase\GetProductCountriesUseCase;

readonly class GetProductCountriesController
{
    public function __construct(
        private GetProductCountriesUseCase $getProductCountriesUseCase,
    ) {
    }

    /**
     * @return FindResponse<ProductCountry>
     */
    #[RpcMethod(
        'marketing.customerHistory.getProductCountries',
        'Получение списка стран продуктов по языку',
        examples: [
            [
                'summary' => 'Список стран продуктов для языка',
                'params'  => [
                    'lang' => 'ru',
                ],
            ],
        ],
    )]
    public function getProductCountries(
        #[RpcParam('Язык')] string $lang,
    ): FindResponse {
        return new FindResponse($this->getProductCountriesUseCase->getProductCountries($lang));
    }
}
