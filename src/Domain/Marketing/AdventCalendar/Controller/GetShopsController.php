<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\AdventCalendar\Entity\Shop;
use App\Domain\Marketing\AdventCalendar\UseCase\GetShopsUseCase;

readonly class GetShopsController
{
    public function __construct(
        private GetShopsUseCase $getShopsUseCase,
    ) {
    }

    /**
     * @return FindResponse<Shop>
     */
    #[RpcMethod(
        'marketing.adventCalendar.getShops',
        'Получение списка магазинов (для фильтра)',
        examples: [
            [
                'summary' => 'Получение списка магазинов',
                'params'  => [
                    "lang" => "ru",
                ],
            ],
        ],
    )]
    public function getShops(
        #[RpcParam('Код языка')] ?string $lang,
    ): FindResponse {
        return new FindResponse($this->getShopsUseCase->getData($lang));
    }
}
