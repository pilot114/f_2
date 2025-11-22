<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use App\Domain\Marketing\AdventCalendar\UseCase\GetAdventCalendarUseCase;

readonly class GetAdventCalendarController
{
    public function __construct(
        private GetAdventCalendarUseCase $getAdventCalendarUseCase,
    ) {
    }

    /**
     * @return FindResponse<AdventItem>
     */
    #[RpcMethod(
        'marketing.adventCalendar.get',
        'Получение списка адвент календаря',
        examples: [
            [
                'summary' => 'Получение списка адвент календаря',
                'params'  => [
                    "shopId" => 'ru',
                ],
            ],
        ],
    )]
    public function get(
        #[RpcParam('ID страны ИМ')] ?string $shopId,
    ): FindResponse {
        return (new FindResponse($this->getAdventCalendarUseCase->getData($shopId)))->recursive();
    }
}
