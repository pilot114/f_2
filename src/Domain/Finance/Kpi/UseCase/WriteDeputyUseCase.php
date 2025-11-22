<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Entity\DeputyUser;
use App\Domain\Finance\Kpi\Repository\DeputyCommandRepository;
use App\Domain\Finance\Kpi\Repository\DeputyUserQueryRepository;
use Database\ORM\Attribute\Loader;
use Database\ORM\QueryRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WriteDeputyUseCase
{
    public function __construct(
        private DeputyCommandRepository $writeRepo,
        /** @var QueryRepositoryInterface<Deputy> */
        private QueryRepositoryInterface $readRepo,
        private DeputyUserQueryRepository $readDeputyUser,
    ) {
    }

    public function create(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $deputyUserId,
        int $currentUserId,
    ): Deputy {
        $deputyUser = $this->readDeputyUser->findByUserId($deputyUserId);
        if (!$deputyUser instanceof DeputyUser) {
            throw new NotFoundHttpException("Не найден пользователь с id: $deputyUserId");
        }
        return $this->writeRepo->createDeputy(new Deputy(
            id: Loader::ID_FOR_INSERT,
            currentUserId: $currentUserId,
            deputyUser: $deputyUser,
            dateStart: $startDate,
            dateEnd: $endDate,
        ));
    }

    public function update(int $deputyId, DateTimeImmutable $startDate, DateTimeImmutable $endDate, int $deputyUserId): Deputy
    {
        $deputy = $this->readRepo->findOrFail($deputyId, 'Не найден заместитель');
        $deputyUser = $this->readDeputyUser->findByUserId($deputyUserId);
        if (!$deputyUser instanceof DeputyUser) {
            throw new NotFoundHttpException("Не найден пользователь с id: $deputyUserId");
        }
        $deputy->update($startDate, $endDate, $deputyUser);
        return $this->writeRepo->updateDeputy($deputy);
    }

    public function delete(int $deputyId): bool
    {
        $this->readRepo->findOrFail($deputyId, 'Не найден заместитель');
        return $this->writeRepo->deleteDeputy($deputyId);
    }
}
