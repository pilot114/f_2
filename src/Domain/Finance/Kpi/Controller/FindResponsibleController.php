<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;

class FindResponsibleController
{
    public function __construct(
        private KpiResponsibleQueryRepository $repo,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.findResponsible',
        'Поиск сотрудников для назначения',
        examples: [
            [
                'summary' => 'Поиск сотрудников для назначения',
                'params'  => [
                    'q' => 'test',
                ],
            ],
        ],
    )]
    /**
     * @return array<array{
     *      id: int,
     *      name: string,
     *      responseName: string,
     *      departmentName: string,
     * }>
     */
    public function findResponsible(string $q): array
    {
        return $this->repo->findResponsible($q);
    }

    #[RpcMethod(
        'finance.kpi.findEnterprises',
        'Поиск предприятий',
        examples: [
            [
                'summary' => 'Поиск предприятий',
                'params'  => [
                    'q' => 'test',
                ],
            ],
        ],
    )]
    /**
     * @return array<array{
     *      id: int,
     *      name: string,
     * }>
     */
    public function findEnterprises(string $q): array
    {
        return $this->repo->findEnterprises($q);
    }
}
