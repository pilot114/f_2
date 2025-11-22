<?php

declare(strict_types=1);

namespace App\Domain\Finance\SelfEmployed\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Finance\SelfEmployed\UseCase\SelfEmployedLimitUseCase;
use DateTimeImmutable;

class SelfEmployedLimitController
{
    public function __construct(
        private SelfEmployedLimitUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.selfEmployed.taxLimit',
        'Отчёт по превышению лимита выплат по самозанятым',
    )]
    /**
     * @return array{
     *     items: array{
     *       check_sum: string,
     *       contract: string,
     *       name: string,
     *       new_dog_date: string,
     *       new_dogovor: string,
     *       summa: string,
     *     },
     *     total: int,
     *
     * }
     */
    public function __invoke(
        #[RpcParam('Первая дата периода')] DateTimeImmutable    $dateFrom,
        #[RpcParam('Последняя дата периода')] DateTimeImmutable $dateTill,
    ): array {
        $data = $this->useCase->getReport($dateFrom, $dateTill);
        return [
            'items' => $data,
            'total' => count($data),
        ];
    }
}
