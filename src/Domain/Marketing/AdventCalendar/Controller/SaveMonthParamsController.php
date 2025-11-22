<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Marketing\AdventCalendar\DTO\CreateMonthRequest;
use App\Domain\Marketing\AdventCalendar\DTO\SaveMonthParamsRequest;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveMonthParamsUseCase;

readonly class SaveMonthParamsController
{
    public function __construct(
        private SaveMonthParamsUseCase $saveMonthParamsUseCase,
    ) {
    }

    #[RpcMethod(
        'marketing.adventCalendar.createMonth',
        'Создание нового месяца календаря',
        examples: [
            [
                'summary' => 'Создание нового месяца календаря',
                'params'  => [
                    "year"  => 2025,
                    "month" => 12,
                    "shop"  => "it",
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
    public function createMonth(
        #[RpcParam('Параметры месяц')] CreateMonthRequest $monthParams,
    ): array {
        return [
            'id' => $this->saveMonthParamsUseCase->createMonth($monthParams),
        ];
    }

    #[RpcMethod(
        'marketing.adventCalendar.saveMonth',
        'Сохранение параметров месяца',
        examples: [
            [
                'summary' => 'Сохранение параметров месяца',
                'params'  => [
                    "calendarId" => 4,
                    "langs"      => [
                        [
                            "lang"  => "it",
                            "title" => "title",
                            "label" => "label",
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function saveMonthParams(
        #[RpcParam('Параметры месяц')] SaveMonthParamsRequest $monthParams,
    ): true {
        return $this->saveMonthParamsUseCase->saveMonthParams($monthParams);
    }
}
