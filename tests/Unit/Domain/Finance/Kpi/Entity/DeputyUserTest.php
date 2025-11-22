<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\DeputyUser;
use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;

it('creates deputy user with id and name', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Manager', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'IT Department');

    $user = new DeputyUser(
        id: 1,
        name: 'Иван Иванов',
        departments: [$department],
        positions: [$position],
    );

    expect($user->id)->toBe(1);
});

it('converts to array with position and department', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Developer', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Engineering');

    $user = new DeputyUser(
        id: 10,
        name: 'Петр Петров',
        departments: [$department],
        positions: [$position],
    );

    $result = $user->toArray();

    expect($result['id'])->toBe(10)
        ->and($result['name'])->toBe('Петр Петров')
        ->and($result['positionName'])->toBe('Developer')
        ->and($result['departmentName'])->toBe('Engineering');
});

it('gets main position name from first position', function (): void {
    $position1 = new KpiDepartmentState(id: 1, name: 'Senior Developer', isBoss: false);
    $position2 = new KpiDepartmentState(id: 2, name: 'Team Lead', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'IT');

    $user = new DeputyUser(
        id: 1,
        name: 'Test User',
        departments: [$department],
        positions: [$position1, $position2],
    );

    $result = $user->toArray();

    expect($result['positionName'])->toBe('Senior Developer');
});

it('gets main department name from first department', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Manager', isBoss: true);
    $department1 = new CpDepartment(id: 1, name: 'Sales');
    $department2 = new CpDepartment(id: 2, name: 'Marketing');

    $user = new DeputyUser(
        id: 1,
        name: 'Test User',
        departments: [$department1, $department2],
        positions: [$position],
    );

    $result = $user->toArray();

    expect($result['departmentName'])->toBe('Sales');
});

it('handles cyrillic names', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Менеджер', isBoss: true);
    $department = new CpDepartment(id: 1, name: 'Отдел продаж');

    $user = new DeputyUser(
        id: 1,
        name: 'Алексей Сидоров',
        departments: [$department],
        positions: [$position],
    );

    $result = $user->toArray();

    expect($result['name'])->toBe('Алексей Сидоров')
        ->and($result['positionName'])->toBe('Менеджер')
        ->and($result['departmentName'])->toBe('Отдел продаж');
});

it('toArray contains all required fields', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Position', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Department');

    $user = new DeputyUser(
        id: 1,
        name: 'User',
        departments: [$department],
        positions: [$position],
    );

    $result = $user->toArray();

    expect($result)->toHaveKeys(['id', 'name', 'positionName', 'departmentName'])
        ->and($result)->toHaveCount(4);
});

it('id is readonly', function (): void {
    $position = new KpiDepartmentState(id: 1, name: 'Position', isBoss: false);
    $department = new CpDepartment(id: 1, name: 'Department');

    $user = new DeputyUser(
        id: 100,
        name: 'Test',
        departments: [$department],
        positions: [$position],
    );

    expect($user->id)->toBe(100);
});
