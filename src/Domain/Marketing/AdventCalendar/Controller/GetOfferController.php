<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use App\Domain\Marketing\AdventCalendar\UseCase\GetOfferUseCase;

readonly class GetOfferController
{
    public function __construct(
        private GetOfferUseCase $getOfferUseCase,
    ) {
    }

    #[RpcMethod(
        'marketing.adventCalendar.getOffer',
        'Получение полной информации о предложении',
        examples: [
            [
                'summary' => 'Список предложений',
                'params'  => [
                    "id" => 3,
                ],
            ],
        ],
    )]
    public function getOffer(
        #[RpcParam('ID Предложения')] int $id,
    ): ?Offer {
        return $this->getOfferUseCase->getData($id);
    }
}
