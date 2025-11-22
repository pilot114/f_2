<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Entity\Employee;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use App\Domain\Hr\Achievements\Repository\EmployeeQueryRepository;
use App\Domain\Hr\Achievements\UseCase\EmployeeAchievementsUseCase;
use Database\ORM\CommandRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->readRepository = $this->createMock(AchievementEmployeeItemQueryRepository::class);
    $this->writeRepository = $this->createMock(CommandRepositoryInterface::class);
    $this->employeeRepository = $this->createMock(EmployeeQueryRepository::class);
    $this->achievementRepository = $this->createMock(AchievementQueryRepository::class);

    $this->useCase = new EmployeeAchievementsUseCase(
        $this->readRepository,
        $this->writeRepository,
        $this->employeeRepository,
        $this->achievementRepository
    );
});

it('gets all achievement employee items', function (): void {
    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $achievement1 = new Achievement(1, 'Achievement 1', 'Description 1', $image);
    $achievement2 = new Achievement(2, 'Achievement 2', 'Description 2', $image);

    $items = new Collection([
        new AchievementEmployeeItem(1, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement1),
        new AchievementEmployeeItem(2, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement2),
    ]);

    $this->readRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($items);

    $result = $this->useCase->getAchievementEmployeeItems();

    expect($result)->toBe($items);
    expect($result)->toHaveCount(2);
});

it('deletes achievement successfully', function (): void {
    $this->writeRepository
        ->expects($this->once())
        ->method('delete')
        ->with(1)
        ->willReturn(true);

    $result = $this->useCase->deleteAchievement(1);

    expect($result)->toBeTrue();
});

it('unlocks achievement for user', function (): void {
    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $achievement = new Achievement(1, 'Test Achievement', 'Description', $image);
    $receiveDate = new DateTimeImmutable('2024-01-15');

    $this->achievementRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($achievement);

    $this->employeeRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($employee);

    $this->readRepository
        ->expects($this->once())
        ->method('employeeAchievementExistsInMonth')
        ->willReturn(false);

    $createdItem = new AchievementEmployeeItem(
        1,
        $receiveDate,
        new DateTimeImmutable(),
        $employee,
        $achievement
    );

    $this->writeRepository
        ->expects($this->once())
        ->method('create')
        ->willReturn($createdItem);

    $result = $this->useCase->unlockAchievement(1, 1, $receiveDate);

    expect($result)->toBe($createdItem);
});

it('throws exception when unlocking non-existent achievement', function (): void {
    $receiveDate = new DateTimeImmutable('2024-01-15');

    $this->achievementRepository
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    expect(fn () => $this->useCase->unlockAchievement(999, 1, $receiveDate))
        ->toThrow(NotFoundHttpException::class, 'Не найдено достижение c id = 999');
});

it('throws exception when achievement already exists in month', function (): void {
    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $achievement = new Achievement(1, 'Test Achievement', 'Description', $image);
    $receiveDate = new DateTimeImmutable('2024-01-15');

    $this->achievementRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($achievement);

    $this->employeeRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($employee);

    $this->readRepository
        ->expects($this->once())
        ->method('employeeAchievementExistsInMonth')
        ->willReturn(true);

    expect(fn () => $this->useCase->unlockAchievement(1, 1, $receiveDate))
        ->toThrow(InvariantDomainException::class, 'Пользователю уже выдано достижение в этом месяце');
});

it('gets achievement unlockers', function (): void {
    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $achievement1 = new Achievement(1, 'Achievement 1', 'Description 1', $image);
    $achievement2 = new Achievement(2, 'Achievement 2', 'Description 2', $image);

    $unlockers = new Collection([
        new AchievementEmployeeItem(1, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement1),
        new AchievementEmployeeItem(2, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement2),
    ]);

    $this->readRepository
        ->expects($this->once())
        ->method('getAchievementUnlockers')
        ->with(1)
        ->willReturn($unlockers);

    $result = $this->useCase->getAchievementUnlockers(1);

    expect($result)->toBe($unlockers);
});

it('edits achievement record', function (): void {
    $employee = new Employee(2, 'Jane Doe', 'Manager');
    $image = new Image(1, 456, 'Test Image');
    $achievement = new Achievement(2, 'New Achievement', 'Description', $image);
    $receiveDate = new DateTimeImmutable('2024-01-15');

    $existingRecord = new AchievementEmployeeItem(
        1,
        new DateTimeImmutable('2024-01-10'),
        new DateTimeImmutable('2024-01-11'),
        $employee,
        $achievement
    );

    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($existingRecord);

    $this->employeeRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(2, 'Не найден пользователь')
        ->willReturn($employee);

    $this->achievementRepository
        ->expects($this->once())
        ->method('getById')
        ->with(2)
        ->willReturn($achievement);

    $this->readRepository
        ->expects($this->once())
        ->method('employeeAchievementExistsInMonth')
        ->willReturn(false);

    $this->writeRepository
        ->expects($this->once())
        ->method('update')
        ->with($existingRecord)
        ->willReturn($existingRecord);

    $result = $this->useCase->editAchievementRecord(1, 2, 2, $receiveDate);

    expect($result)->toBe($existingRecord);
});

it('throws exception when editing non-existent record', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    expect(fn () => $this->useCase->editAchievementRecord(999, null, null, null))
        ->toThrow(NotFoundHttpException::class, 'Не найдена запись с id = 999 о присвоении награды');
});
