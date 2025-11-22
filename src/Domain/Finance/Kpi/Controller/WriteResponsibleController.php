<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\UseCase\WriteResponsibleUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class WriteResponsibleController
{
    public function __construct(
        private WriteResponsibleUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.createResponsible',
        'Назначение ответственного за получение файлов по KPI',
        examples: [
            [
                'summary' => 'назначение ответственного',
                'params'  => [
                    'enterpriseId' => 79690841701,
                    'userId'       => 4026,
                ],
            ],
        ]
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
    public function create(
        int $enterpriseId,
        int $userId,
    ): array {
        return $this->useCase->create($enterpriseId, $userId, $this->currentUser->id)->toArray();
    }

    #[RpcMethod(
        'finance.kpi.updateResponsible',
        'Обновление ответственного за получение файлов по KPI',
        examples: [
            [
                'summary' => 'обновление ответственного',
                'params'  => [
                    'responsibleId' => 42,
                    'enterpriseId'  => 79690841701,
                    'userId'        => 4026,
                ],
            ],
        ]
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
    public function update(
        int $responsibleId,
        int $enterpriseId,
        int $userId,
    ): array {
        return $this->useCase->update($responsibleId, $enterpriseId, $userId, $this->currentUser->id)->toArray();
    }

    #[RpcMethod(
        'finance.kpi.deleteResponsible',
        'Удаление ответственного за получение файлов по KPI',
        examples: [
            [
                'summary' => 'удаление ответственного',
                'params'  => [
                    'responsibleId' => 1234,
                ],
            ],
        ]
    )]
    public function delete(int $responsibleId): bool
    {
        return $this->useCase->delete($responsibleId);
    }
}
