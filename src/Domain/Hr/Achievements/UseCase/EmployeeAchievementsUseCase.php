<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Entity\Employee;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use App\Domain\Hr\Achievements\Repository\EmployeeQueryRepository;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmployeeAchievementsUseCase
{
    /**
     * @param CommandRepositoryInterface<AchievementEmployeeItem> $writeRepository
     */
    public function __construct(
        private AchievementEmployeeItemQueryRepository $readRepository,
        private CommandRepositoryInterface $writeRepository,
        private EmployeeQueryRepository $employeeRepository,
        private AchievementQueryRepository $achievementRepository,
    ) {
    }

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getAchievementEmployeeItems(): Enumerable
    {
        return $this->readRepository->getAll();
    }

    public function deleteAchievement(int $id): bool
    {
        return $this->writeRepository->delete($id);
    }

    public function unlockAchievement(int $achievementId, int $userId, DateTimeImmutable $receiveDate): AchievementEmployeeItem
    {
        $achievement = $this->achievementRepository->getById($achievementId);
        if (!$achievement instanceof Achievement) {
            throw new NotFoundHttpException("Не найдено достижение c id = $achievementId");
        }
        $employee = $this->employeeRepository->getById($userId);
        if (!$employee instanceof Employee) {
            throw new NotFoundHttpException("Не найден пользователь c id = $userId");
        }

        $entity = new AchievementEmployeeItem(
            id: Loader::ID_FOR_INSERT,
            received: $receiveDate,
            added: new DateTimeImmutable(),
            employee: $employee,
            achievement: $achievement,
        );

        $entity->setReceiveDateWithCheckCurrentDate($receiveDate);
        $this->checkAlreadyExist($entity, $receiveDate);

        return $this->writeRepository->create($entity);
    }

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getAchievementUnlockers(int $achievementId): Enumerable
    {
        return $this->readRepository->getAchievementUnlockers($achievementId);
    }

    public function editAchievementRecord(
        int $id,
        ?int $userId,
        ?int $achievementId,
        ?DateTimeImmutable $receiveDate,
    ): AchievementEmployeeItem {
        $record = $this->readRepository->getById($id);
        if (!$record instanceof AchievementEmployeeItem) {
            throw new NotFoundHttpException("Не найдена запись с id = $id о присвоении награды");
        }
        if ($userId !== null) {
            $employee = $this->employeeRepository->findOrFail($userId, 'Не найден пользователь');
            $record->setEmployee($employee);
        }

        if ($achievementId !== null) {
            $achievement = $this->achievementRepository->getById($achievementId);
            if (!$achievement instanceof Achievement) {
                throw new NotFoundHttpException('Не найдено достижение c id = ' . $achievementId);
            }
            $record->setAchievement($achievement);
        }

        if ($receiveDate instanceof DateTimeImmutable) {
            $this->checkAlreadyExist($record, $receiveDate);
            $record->setReceiveDateWithCheckCurrentDate($receiveDate);
        }

        return $this->writeRepository->update($record);
    }

    private function checkAlreadyExist(AchievementEmployeeItem $record, DateTimeImmutable $receiveDate): void
    {
        $alreadyExist = $this->readRepository->employeeAchievementExistsInMonth(
            $record->getEmployeeId(),
            $record->getAchievementId(),
            $receiveDate,
            $record->id
        );

        if ($alreadyExist) {
            throw new InvariantDomainException('Пользователю уже выдано достижение в этом месяце');
        }
    }
}
