<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleEnterprise;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleUser;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use DateTimeImmutable;

class WriteResponsibleUseCase
{
    public function __construct(
        /** @var CommandRepositoryInterface<KpiResponsible> */
        private CommandRepositoryInterface $writeRepo,
        private KpiResponsibleQueryRepository $readRepo,
    ) {
    }

    public function create(int $enterpriseId, int $userId, int $currentUserId): KpiResponsible
    {
        $responsible = $this->writeRepo->create(new KpiResponsible(
            id: Loader::ID_FOR_INSERT,
            user: new KpiResponsibleUser(
                id: $userId
            ),
            enterprise: new KpiResponsibleEnterprise(
                id: $enterpriseId
            ),
            changeDate: new DateTimeImmutable(),
            changeUserId: $currentUserId,
        ));
        return $this->readRepo->getResponsible($responsible->id);
    }

    public function update(int $responsibleId, int $enterpriseId, int $userId, int $currentUserId): KpiResponsible
    {
        $responsible = $this->readRepo->findOrFail($responsibleId, 'Не найден ответственный за KPI');
        $responsible->update($enterpriseId, $userId, $currentUserId);
        $responsible = $this->writeRepo->update($responsible);
        return $this->readRepo->getResponsible($responsible->id);
    }

    public function delete(int $responsibleId): bool
    {
        return $this->writeRepo->delete($responsibleId);
    }
}
