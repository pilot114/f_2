<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use App\Domain\Marketing\CustomerHistory\Enum\Status;
use App\Domain\Marketing\CustomerHistory\UseCase\GetCustomerHistoryUseCase;
use DateTimeImmutable;

readonly class GetCustomerHistoryController
{
    public function __construct(
        private GetCustomerHistoryUseCase $getCustomerHistoryUseCase,
    ) {
    }

    /**
     * @return FindResponse<HistoryItem>
     */
    #[RpcMethod(
        'marketing.customerHistory.get',
        'Получение истории клиентов',
        examples: [
            [
                'summary' => 'Список истории клиентов',
                'params'  => [
                    'q'        => 'Иванов Иван Иванович',
                    'state'    => 1,
                    'lang'     => 'en',
                    'dateFrom' => '2020-01-01',
                    'dateTill' => '2020-12-31',
                    'perPage'  => 10,
                    'page'     => 1,
                ],
            ],
        ],
    )]
    public function get(
        #[RpcParam('Количество записей')] int $perPage,
        #[RpcParam('Страница')] int $page,
        #[RpcParam('Поиск')] ?string $q = null,
        #[RpcParam('Статус')] ?Status $state = null,
        #[RpcParam('Язык')] ?string $lang = null,
        #[RpcParam('Начало периода')] ?DateTimeImmutable $dateFrom = null,
        #[RpcParam('Окончание периода')] ?DateTimeImmutable $dateTill = null,
    ): FindResponse {

        return (new FindResponse($this->getCustomerHistoryUseCase->getData(
            q: $q,
            state: $state,
            lang: $lang,
            dateFrom: $dateFrom,
            dateTill: $dateTill,
            page: $page,
            perPage: $perPage,
        ), 0))->recursive();
    }
}
