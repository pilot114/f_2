<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;
use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use App\Domain\Finance\Kpi\Enum\KpiType;
use DateTimeImmutable;

it('creates kpi department with basic fields', function (): void {
    $department = new KpiDepartment(
        id: 1,
        name: 'IT Department',
        parentId: 0,
        level: 1,
    );

    expect($department->getId())->toBe(1)
        ->and($department->getLevel())->toBe(1)
        ->and($department->getParentId())->toBe(0);
});

it('converts to array without employees', function (): void {
    $department = new KpiDepartment(
        id: 1,
        name: 'Sales Department',
        parentId: 0,
        level: 1,
    );

    $result = $department->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Sales Department')
        ->and($result['parentId'])->toBe(0)
        ->and($result['level'])->toBe(1)
        ->and($result['emps'])->toBeArray()
        ->and($result['emps'])->toBeEmpty()
        ->and($result['countActualEmptyKpi'])->toBe(0);
});

it('converts to array with employees', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'John Doe',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    $department = new KpiDepartment(
        id: 1,
        name: 'IT Department',
        parentId: 0,
        level: 1,
        emps: [$employee],
    );

    $result = $department->toArray();

    expect($result['emps'])->toHaveCount(1)
        ->and($result['emps'][0]['id'])->toBe(1)
        ->and($result['emps'][0]['name'])->toBe('John Doe')
        ->and($result['emps'][0]['positionName'])->toBe('Manager')
        ->and($result['emps'][0]['isBoss'])->toBeTrue();
});

it('yields kpi from employees', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Jane Doe',
        hasUserpic: false,
        kpi: [$kpi],
        positions: [$position],
    );

    $department = new KpiDepartment(
        id: 1,
        name: 'IT Department',
        parentId: 0,
        level: 1,
        emps: [$employee],
    );

    $kpiList = iterator_to_array($department->getKpi());

    expect($kpiList)->toHaveCount(1)
        ->and($kpiList[0])->toBe($kpi);
});

it('counts actual empty kpi across employees', function (): void {
    // Create an actual period KPI (next month from today)
    $actualDate = (new DateTimeImmutable())->modify('first day of last month');

    $emptyKpi = new Kpi(
        id: 1,
        billingMonth: $actualDate,
        type: KpiType::MONTHLY,
        value: null, // empty
    );

    $filledKpi = new Kpi(
        id: 2,
        billingMonth: $actualDate,
        type: KpiType::MONTHLY,
        value: 75, // filled
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee1 = new KpiEmployee(
        id: 1,
        name: 'Employee 1',
        hasUserpic: false,
        kpi: [$emptyKpi],
        positions: [$position],
    );

    $employee2 = new KpiEmployee(
        id: 2,
        name: 'Employee 2',
        hasUserpic: false,
        kpi: [$filledKpi],
        positions: [$position],
    );

    $department = new KpiDepartment(
        id: 1,
        name: 'IT Department',
        parentId: 0,
        level: 1,
        emps: [$employee1, $employee2],
    );

    $result = $department->toArray();

    expect($result['countActualEmptyKpi'])->toBe(1);
});

it('handles multiple employees with different kpi', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $kpi1 = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 80,
    );

    $kpi2 = new Kpi(
        id: 2,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::QUARTERLY,
        value: 90,
    );

    $employee1 = new KpiEmployee(
        id: 1,
        name: 'Alice',
        hasUserpic: false,
        kpi: [$kpi1],
        positions: [$position],
    );

    $employee2 = new KpiEmployee(
        id: 2,
        name: 'Bob',
        hasUserpic: false,
        kpi: [$kpi2],
        positions: [$position],
    );

    $department = new KpiDepartment(
        id: 1,
        name: 'Engineering',
        parentId: 0,
        level: 2,
        emps: [$employee1, $employee2],
    );

    $allKpi = iterator_to_array($department->getKpi());

    expect($allKpi)->toHaveCount(2)
        ->and($department->toArray()['emps'])->toHaveCount(2);
});
