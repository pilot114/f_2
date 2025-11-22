<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveProductUseCase;

readonly class SaveProductController
{
    public function __construct(
        private SaveProductUseCase $saveMonthProductUseCase,
    ) {
    }

    #[RpcMethod(
        'marketing.adventCalendar.addProducts',
        'Добавление продукта к адвент календарю',
        examples: [
            [
                'summary' => 'Добавление продукта в адвент календарь',
                'params'  => [
                    "calendarId" => 1,
                    "code"       => ["500020", "500632"],
                ],
            ],
        ],
    )]
    public function addProductToCalendar(
        #[RpcParam('ID Календаря')] int $calendarId,
        #[RpcParam('Коды продуктов')] array $code,
    ): true {
        return $this->saveMonthProductUseCase->saveProduct($calendarId, $code);
    }

    #[RpcMethod(
        'marketing.adventCalendar.removeProducts',
        'Удаление продукта из адвент календаря',
        examples: [
            [
                'summary' => 'Удаление продукта из адвент календаря',
                'params'  => [
                    "productIds" => [1, 2, 3],
                ],
            ],
        ],
    )]
    public function removeProductFromCalendar(
        #[RpcParam('ID записей продуктов')] array $productIds,
    ): true {
        return $this->saveMonthProductUseCase->removeProduct($productIds);
    }

}
