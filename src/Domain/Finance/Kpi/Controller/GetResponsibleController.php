<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\UseCase\GetResponsibleUseCase;

class GetResponsibleController
{
    public function __construct(
        private GetResponsibleUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getResponsible',
        'Получение ответственного за получение файлов по KPI',
    )]
    /**
     * @return array{
     *     id: int,
     *     user: array{
     *         id: int,
     *         name: string,
     *         responseName: string,
     *     },
     *     enterprise: array{
     *         id: int,
     *         name: string,
     *     },
     * }
     */
    public function __invoke(int $id): array
    {
        return $this->useCase->get($id)->toArray();
    }
}
