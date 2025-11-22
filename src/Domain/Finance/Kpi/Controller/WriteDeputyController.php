<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Finance\Kpi\UseCase\WriteDeputyUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use DateTimeImmutable;

class WriteDeputyController
{
    public function __construct(
        private WriteDeputyUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.createDeputy',
        'Назначение заместителя текущему пользователю',
        examples: [
            [
                'summary' => 'назначение заместителя',
                'params'  => [
                    'startDate'    => '01.03.2025',
                    'endDate'      => '31.03.2025',
                    'deputyUserId' => 5555,
                ],
            ],
        ]
    )]
    /**
     * @return array{
     *     id: int,
     *     dateStart: string,
     *     dateEnd: string,
     *     deputyUser: array{
     *         id: int,
     *         name: string,
     *         positionName: string,
     *         departmentName: string,
     *     },
     * }
     */
    public function create(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $deputyUserId,
    ): array {
        $deputy = $this->useCase->create($startDate, $endDate, $deputyUserId, $this->currentUser->id);
        return $deputy->toArray();
    }

    #[RpcMethod(
        'finance.kpi.updateDeputy',
        'Обновление заместителя текущего пользователя',
        examples: [
            [
                'summary' => 'обновление заместителя',
                'params'  => [
                    'deputyId'     => 1234,
                    'startDate'    => '01.03.2025',
                    'endDate'      => '31.10.2025',
                    'deputyUserId' => 4026,
                ],
            ],
        ]
    )]
    /**
     * @return array{
     *     id: int,
     *     dateStart: string,
     *     dateEnd: string,
     *     deputyUser: array{
     *         id: int,
     *         name: string,
     *         positionName: string,
     *         departmentName: string,
     *     },
     * }
     */
    public function update(
        int $deputyId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $deputyUserId,
    ): array {
        $deputy = $this->useCase->update($deputyId, $startDate, $endDate, $deputyUserId);
        return $deputy->toArray();
    }

    #[RpcMethod(
        'finance.kpi.deleteDeputy',
        'Удаление заместителя текущего пользователя',
        examples: [
            [
                'summary' => 'удаление заместителя',
                'params'  => [
                    'deputyId' => 1234,
                ],
            ],
        ]
    )]
    public function delete(int $deputyId): bool
    {
        return $this->useCase->delete($deputyId);
    }
}
