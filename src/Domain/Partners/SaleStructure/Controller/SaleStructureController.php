<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use App\Domain\Partners\SaleStructure\UseCase\PartnerInfoUseCase;
use App\Domain\Partners\SaleStructure\UseCase\SaleStructureUseCase;
use DateTimeImmutable;

class SaleStructureController
{
    public function __construct(
        private SaleStructureUseCase $salesCase,
        private PartnerInfoUseCase    $partnerCase,
    ) {
    }

    #[RpcMethod(
        'partners.saleStructure.getPartnerInfo',
        'Получить данные по партнёру',
        examples: [
            [
                'summary' => 'Получить данные по партнёру, контракт 3278700',
                'params'  => [
                    'contract' => '3278700',
                ],
            ],
        ],
    )]
    public function getPartner(#[RpcParam('Контракт')] string $contract): PartnerInfo
    {
        return $this->partnerCase->getByContract($contract);
    }

    #[RpcMethod(
        'partners.saleStructure.getSalesStructure',
        'Получить данные по структуре продаж',
        examples: [
            [
                'summary' => 'Получить данные по структуре продаж за май и июнь 2025, контракт 3278700',
                'params'  => [
                    'contract' => '3278700',
                    'from'     => '2025-05',
                    'till'     => '2025-06',
                ],
            ],
        ],
    )]
    /**
     * @return array{
     *     period: string,
     *     countries: array{
     *         id: int,
     *         name: string,
     *         currency: string,
     *         percent: string,
     *         points: string
     *     }[]
     * }[]
     */
    public function getSalesStructure(
        #[RpcParam('Контракт')] string $contract,
        #[RpcParam('Начало периода')] DateTimeImmutable $from,
        #[RpcParam('Конец периода')] DateTimeImmutable $till
    ): array {
        return $this->salesCase->get($contract, $from, $till);
    }
}
