<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Marketing\AdventCalendar\DTO\SaveOfferRequest;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveOfferUseCase;

readonly class SaveOfferController
{
    public function __construct(
        private SaveOfferUseCase $saveOfferUseCase,
    ) {
    }

    #[RpcMethod(
        'marketing.adventCalendar.saveOffer',
        'Добавление предложения к адвент-календарю',
        examples: [
            [
                'summary' => 'Добавление предложения',
                'params'  => [
                    "calendarId" => 1,
                    "active"     => 1,
                    "bkImageId"  => 123,
                    "offerId"    => 1,
                    "langs"      => [
                        [
                            "lang"            => "ru",
                            "typeName"        => "Скидка",
                            "buttonText"      => "Купить",
                            "shortTitle"      => "Заголовок",
                            "shortDescr"      => "Краткое описание",
                            "fullDescription" => "Полное описание",
                            "imageId"         => 700612,
                            "newsLink"        => "https://example.com/news",
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    /**
     * @return array<array{
     *     id: int
     * }>
     */
    public function saveOffer(
        #[RpcParam('Параметры предложения')] SaveOfferRequest $offerRequest,
    ): array {
        return [
            'id' => $this->saveOfferUseCase->saveOffer($offerRequest),
        ];
    }

    #[RpcMethod(
        'marketing.adventCalendar.removeOffer',
        'Удаление предложения из адвент-календаря',
        examples: [
            [
                'summary' => 'Удаление предложения',
                'params'  => [
                    "id"         => 1,
                    "calendarId" => 1,
                ],
            ],
        ]
    )]
    public function removeOffer(
        #[RpcParam('id')] int $id,
        #[RpcParam('calendarId')] int $calendarId,
    ): true {
        return $this->saveOfferUseCase->removeOffer($id, $calendarId);
    }
}
