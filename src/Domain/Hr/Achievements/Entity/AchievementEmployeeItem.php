<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemResponse;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemWithEditorResponse;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemWithoutAchievementResponse;
use Database\ORM\Attribute\{Column, Entity};
use DateTimeImmutable;
use LogicException;

#[Entity(name: 'test.cp_ea_employee_achievments', sequenceName: 'TEST.cp_ea_employee_achievments_SQ')]
class AchievementEmployeeItem
{
    public function __construct(
        #[Column(name: 'id')] public int $id,
        #[Column(name: 'receive_date')] private DateTimeImmutable $received,
        #[Column(name: 'add_date')] private DateTimeImmutable $added,
        #[Column(name: 'cp_emp_id')] private Employee $employee,
        #[Column(name: 'achievement_cards_id')] private Achievement $achievement,
        #[Column(name: 'editor', onlyForRead: true)] private ?Employee $lastEditor = null,
    ) {
    }

    public function getEmployeeId(): int
    {
        return $this->employee->id;
    }

    public function getAchievementId(): int
    {
        return $this->achievement->id;
    }

    public function getReceived(): DateTimeImmutable
    {
        return $this->received;
    }

    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }

    public function setAchievement(Achievement $achievement): void
    {
        $this->achievement = $achievement;
    }

    public function setReceiveDateWithCheckCurrentDate(DateTimeImmutable $receiveDate): void
    {
        $receiveDateMonth = $receiveDate->modify('first day of this month');
        $currentMonth = (new DateTimeImmutable())->modify('first day of this month');

        if ($receiveDateMonth > $currentMonth) {
            throw new InvariantDomainException('нельзя создавать достижения в будущих месяцах');
        }
        $this->received = $receiveDate;
    }

    public function toAchievementEmployeeItemWithEditorResponse(): AchievementEmployeeItemWithEditorResponse
    {
        if (!$this->lastEditor instanceof Employee) {
            throw new LogicException('Нельзя импортировать в AchievementEmployeeItemWithEditorResponse');
        }

        return new AchievementEmployeeItemWithEditorResponse(
            id: $this->id,
            receiveDate: $this->received,
            addedDate: $this->added,
            employee: $this->employee->toEmployeeResponse(),
            achievement: $this->achievement->toAchievementResponse(),
            editor: $this->lastEditor->toEmployeeResponse(),
        );
    }

    public function toAchievementEmployeeItemResponse(): AchievementEmployeeItemResponse
    {
        return new AchievementEmployeeItemResponse(
            id: $this->id,
            receiveDate: $this->received,
            addedDate: $this->added,
            employee: $this->employee->toEmployeeResponse(),
            achievement: $this->achievement->toAchievementResponse(withoutAchievements: true),
        );
    }

    public function toAchievementEmployeeItemWithoutAchievementResponse(): AchievementEmployeeItemWithoutAchievementResponse
    {
        return new AchievementEmployeeItemWithoutAchievementResponse(
            id: $this->id,
            receiveDate: $this->received,
            addedDate: $this->added,
            employee: $this->employee->toEmployeeResponse(),
        );
    }
}
