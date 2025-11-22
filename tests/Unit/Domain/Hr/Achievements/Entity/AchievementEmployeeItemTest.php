<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemWithEditorResponse;
use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Entity\Employee;
use App\Domain\Hr\Achievements\Entity\Image;
use DateTimeImmutable;
use LogicException;

it('creates achievement employee item with all fields', function (): void {
    $received = new DateTimeImmutable('2024-01-15');
    $added = new DateTimeImmutable('2024-01-10');
    $employee = new Employee(id: 1, name: 'John Doe', positionName: 'Manager');
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $category = new Category(id: 1, name: 'Excellence', isPersonal: 1, isHidden: 0, color: $color);
    $achievement = new Achievement(
        id: 1,
        name: 'Best Employee',
        description: 'Top performer',
        image: $image,
        category: $category,
    );

    $item = new AchievementEmployeeItem(
        id: 1,
        received: $received,
        added: $added,
        employee: $employee,
        achievement: $achievement,
    );

    expect($item->id)->toBe(1)
        ->and($item->getEmployeeId())->toBe(1)
        ->and($item->getAchievementId())->toBe(1)
        ->and($item->getReceived())->toBe($received);
});

it('returns correct employee id', function (): void {
    $employee = new Employee(id: 42, name: 'Test User', positionName: 'Developer');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $achievement,
    );

    expect($item->getEmployeeId())->toBe(42);
});

it('returns correct achievement id', function (): void {
    $employee = new Employee(id: 1, name: 'Test', positionName: 'Analyst');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 99, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $achievement,
    );

    expect($item->getAchievementId())->toBe(99);
});

it('sets employee', function (): void {
    $oldEmployee = new Employee(id: 1, name: 'Old Employee', positionName: 'Old Position');
    $newEmployee = new Employee(id: 2, name: 'New Employee', positionName: 'New Position');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $oldEmployee,
        achievement: $achievement,
    );

    $item->setEmployee($newEmployee);

    expect($item->getEmployeeId())->toBe(2);
});

it('sets achievement', function (): void {
    $employee = new Employee(id: 1, name: 'Test', positionName: 'Tester');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $oldAchievement = new Achievement(id: 1, name: 'Old', description: 'Old', image: $image);
    $newAchievement = new Achievement(id: 2, name: 'New', description: 'New', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $oldAchievement,
    );

    $item->setAchievement($newAchievement);

    expect($item->getAchievementId())->toBe(2);
});

it('sets receive date with valid date', function (): void {
    $employee = new Employee(id: 1, name: 'Test', positionName: 'Engineer');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable('2024-01-01'),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $achievement,
    );

    $newDate = new DateTimeImmutable('2024-02-15');
    $item->setReceiveDateWithCheckCurrentDate($newDate);

    expect($item->getReceived())->toBe($newDate);
});

it('throws exception when setting future receive date', function (): void {
    $employee = new Employee(id: 1, name: 'Test', positionName: 'Staff');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $achievement,
    );

    $futureDate = new DateTimeImmutable('+2 months');

    expect(fn () => $item->setReceiveDateWithCheckCurrentDate($futureDate))
        ->toThrow(InvariantDomainException::class);
});

it('converts to achievement employee item response', function (): void {
    $employee = new Employee(id: 1, name: 'John Doe', positionName: 'Senior Developer');
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $achievement = new Achievement(id: 1, name: 'Best', description: 'Top', image: $image);
    $received = new DateTimeImmutable('2024-01-15');
    $added = new DateTimeImmutable('2024-01-10');

    $item = new AchievementEmployeeItem(
        id: 10,
        received: $received,
        added: $added,
        employee: $employee,
        achievement: $achievement,
    );

    $response = $item->toAchievementEmployeeItemResponse();

    expect($response->id)->toBe(10)
        ->and($response->receiveDate)->toBe($received)
        ->and($response->addedDate)->toBe($added);
});

it('converts to achievement employee item without achievement response', function (): void {
    $employee = new Employee(id: 5, name: 'Jane Smith', positionName: 'Team Lead');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);
    $received = new DateTimeImmutable('2024-03-20');
    $added = new DateTimeImmutable('2024-03-15');

    $item = new AchievementEmployeeItem(
        id: 20,
        received: $received,
        added: $added,
        employee: $employee,
        achievement: $achievement,
    );

    $response = $item->toAchievementEmployeeItemWithoutAchievementResponse();

    expect($response->id)->toBe(20)
        ->and($response->receiveDate)->toBe($received)
        ->and($response->addedDate)->toBe($added);
});

it('throws exception when converting to response with editor without editor', function (): void {
    $employee = new Employee(id: 1, name: 'Test', positionName: 'Admin');
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $item = new AchievementEmployeeItem(
        id: 1,
        received: new DateTimeImmutable(),
        added: new DateTimeImmutable(),
        employee: $employee,
        achievement: $achievement,
        lastEditor: null,
    );

    expect(fn (): AchievementEmployeeItemWithEditorResponse => $item->toAchievementEmployeeItemWithEditorResponse())
        ->toThrow(LogicException::class);
});
