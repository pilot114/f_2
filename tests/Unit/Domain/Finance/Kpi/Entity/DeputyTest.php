<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Entity\DeputyUser;
use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;
use DateTimeImmutable;

it('creates deputy with all fields', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Manager', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'IT');
    $deputyUser = new DeputyUser(
        id: 2,
        name: 'Deputy Name',
        departments: [$department],
        positions: [$position],
    );

    $startDate = new DateTimeImmutable('2024-01-01');
    $endDate = new DateTimeImmutable('2024-12-31');

    $deputy = new Deputy(
        id: 1,
        currentUserId: 10,
        deputyUser: $deputyUser,
        dateStart: $startDate,
        dateEnd: $endDate,
    );

    expect($deputy->id)->toBe(1);
});

it('updates deputy information', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Manager', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'IT');

    $initialDeputyUser = new DeputyUser(
        id: 2,
        name: 'Initial Deputy',
        departments: [$department],
        positions: [$position],
    );

    $newDeputyUser = new DeputyUser(
        id: 3,
        name: 'New Deputy',
        departments: [$department],
        positions: [$position],
    );

    $deputy = new Deputy(
        id: 1,
        currentUserId: 10,
        deputyUser: $initialDeputyUser,
        dateStart: new DateTimeImmutable('2024-01-01'),
        dateEnd: new DateTimeImmutable('2024-06-30'),
    );

    $newStartDate = new DateTimeImmutable('2024-07-01');
    $newEndDate = new DateTimeImmutable('2024-12-31');

    $deputy->update($newStartDate, $newEndDate, $newDeputyUser);

    $result = $deputy->toArray();

    expect($result['dateStart'])->toBe($newStartDate)
        ->and($result['dateEnd'])->toBe($newEndDate)
        ->and($result['deputyUser']['name'])->toBe('New Deputy');
});

it('converts to array', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Developer', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Engineering');
    $deputyUser = new DeputyUser(
        id: 5,
        name: 'Test Deputy',
        departments: [$department],
        positions: [$position],
    );

    $startDate = new DateTimeImmutable('2024-03-01');
    $endDate = new DateTimeImmutable('2024-09-30');

    $deputy = new Deputy(
        id: 100,
        currentUserId: 50,
        deputyUser: $deputyUser,
        dateStart: $startDate,
        dateEnd: $endDate,
    );

    $result = $deputy->toArray();

    expect($result)->toHaveKeys(['id', 'dateStart', 'dateEnd', 'deputyUser'])
        ->and($result['id'])->toBe(100)
        ->and($result['dateStart'])->toBe($startDate)
        ->and($result['dateEnd'])->toBe($endDate)
        ->and($result['deputyUser'])->toBeArray();
});

it('toArray includes deputy user details', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Manager', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'Sales');
    $deputyUser = new DeputyUser(
        id: 10,
        name: 'Иван Иванов',
        departments: [$department],
        positions: [$position],
    );

    $deputy = new Deputy(
        id: 1,
        currentUserId: 1,
        deputyUser: $deputyUser,
        dateStart: new DateTimeImmutable('2024-01-01'),
        dateEnd: new DateTimeImmutable('2024-12-31'),
    );

    $result = $deputy->toArray();

    expect($result['deputyUser']['id'])->toBe(10)
        ->and($result['deputyUser']['name'])->toBe('Иван Иванов');
});

it('handles date range updates', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Position', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Department');
    $deputyUser = new DeputyUser(
        id: 1,
        name: 'User',
        departments: [$department],
        positions: [$position],
    );

    $deputy = new Deputy(
        id: 1,
        currentUserId: 1,
        deputyUser: $deputyUser,
        dateStart: new DateTimeImmutable('2024-01-01'),
        dateEnd: new DateTimeImmutable('2024-03-31'),
    );

    $newStart = new DateTimeImmutable('2024-04-01');
    $newEnd = new DateTimeImmutable('2024-06-30');

    $deputy->update($newStart, $newEnd, $deputyUser);

    $result = $deputy->toArray();

    expect($result['dateStart'])->toBe($newStart)
        ->and($result['dateEnd'])->toBe($newEnd);
});

it('toArray structure is complete', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Position', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Department');
    $deputyUser = new DeputyUser(
        id: 1,
        name: 'User',
        departments: [$department],
        positions: [$position],
    );

    $deputy = new Deputy(
        id: 1,
        currentUserId: 1,
        deputyUser: $deputyUser,
        dateStart: new DateTimeImmutable('2024-01-01'),
        dateEnd: new DateTimeImmutable('2024-12-31'),
    );

    $result = $deputy->toArray();

    expect($result)->toHaveCount(4);
});
