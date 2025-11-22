<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Marketing\CustomerHistory\DTO\EditCustomerHistoryRequest;
use App\Domain\Marketing\CustomerHistory\UseCase\EditCustomerHistoryUseCase;

readonly class WriteCustomerHistoryController
{
    public function __construct(
        private EditCustomerHistoryUseCase $editCustomerHistoryUseCase,
    ) {
    }

    #[RpcMethod(
        'marketing.customerHistory.edit',
        'Создание/Редактирование истории клиентов',
        examples: [
            [
                'summary' => 'Создание/Редактирование истории клиентов',
                'params'  => [
                    'id'         => 1,
                    'status'     => 1,
                    'preview'    => 'Краткое описание истории',
                    'text'       => 'Полный текст истории клиента',
                    'commentary' => 'Комментарий к истории',
                    'shops'      => ['ru', 'en'],
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function edit(
        #[RpcParam('Запрос на редактирование истории клиента')] EditCustomerHistoryRequest $history,
    ): true {
        return $this->editCustomerHistoryUseCase->editCustomerHistory($history);
    }

}
