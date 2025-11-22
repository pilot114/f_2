<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;
use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use App\Domain\Finance\Kpi\Enum\KpiType;
use DateTimeImmutable;

it('creates kpi employee with basic fields', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'John Doe',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    expect(iterator_to_array($employee->getKpi()))->toBeEmpty();
});

it('yields all kpi records', function (): void {
    $kpi1 = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
    );

    $kpi2 = new Kpi(
        id: 2,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::QUARTERLY,
        value: 80,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Jane Doe',
        hasUserpic: false,
        kpi: [$kpi1, $kpi2],
        positions: [$position],
    );

    $kpiList = iterator_to_array($employee->getKpi());

    expect($kpiList)->toHaveCount(2)
        ->and($kpiList[0])->toBe($kpi1)
        ->and($kpiList[1])->toBe($kpi2);
});

it('converts to array with counts', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Alice',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    $result = $employee->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Alice')
        ->and($result['countPastEmptyKpi'])->toBe(0)
        ->and($result['countPastIsFilledAndNotSentKpi'])->toBe(0);
});

it('returns userpics when employee has userpic', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 123,
        name: 'Bob',
        hasUserpic: true,
        kpi: [],
        positions: [$position],
    );

    $result = $employee->getUserPics();

    expect($result['userpicSmall'])->toBeString()
        ->and($result['userpicMedium'])->toBeString()
        ->and($result['userpicSmall'])->toContain('123')
        ->and($result['userpicMedium'])->toContain('123');
});

it('returns null userpics when employee has no userpic', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 456,
        name: 'Charlie',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    $result = $employee->getUserPics();

    expect($result['userpicSmall'])->toBeNull()
        ->and($result['userpicMedium'])->toBeNull();
});

it('returns actual kpi for each type', function (): void {
    $actualDate = (new DateTimeImmutable())->modify('first day of last month');

    $monthlyKpi = new Kpi(
        id: 1,
        billingMonth: $actualDate,
        type: KpiType::MONTHLY,
        value: 75,
    );

    $bimonthlyKpi = new Kpi(
        id: 2,
        billingMonth: $actualDate,
        type: KpiType::BIMONTHLY,
        value: 80,
    );

    $quarterlyKpi = new Kpi(
        id: 3,
        billingMonth: $actualDate,
        type: KpiType::QUARTERLY,
        value: 85,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Dave',
        hasUserpic: false,
        kpi: [$monthlyKpi, $bimonthlyKpi, $quarterlyKpi],
        positions: [$position],
    );

    $result = $employee->getActualKpiEachType();

    expect($result['kpiMonthly'])->toBeArray()
        ->and($result['kpiBimonthly'])->toBeArray()
        ->and($result['kpiQuarterly'])->toBeArray()
        ->and($result['kpiMonthly']['value'])->toBe(75)
        ->and($result['kpiBimonthly']['value'])->toBe(80)
        ->and($result['kpiQuarterly']['value'])->toBe(85);
});

it('returns null for missing kpi types', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Eve',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    $result = $employee->getActualKpiEachType();

    expect($result['kpiMonthly'])->toBeNull()
        ->and($result['kpiBimonthly'])->toBeNull()
        ->and($result['kpiQuarterly'])->toBeNull();
});

it('counts actual empty kpi', function (): void {
    $actualDate = (new DateTimeImmutable())->modify('first day of last month');

    $emptyKpi = new Kpi(
        id: 1,
        billingMonth: $actualDate,
        type: KpiType::MONTHLY,
        value: null,
    );

    $filledKpi = new Kpi(
        id: 2,
        billingMonth: $actualDate,
        type: KpiType::QUARTERLY,
        value: 80,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Frank',
        hasUserpic: false,
        kpi: [$emptyKpi, $filledKpi],
        positions: [$position],
    );

    expect($employee->countActualEmptyKpi())->toBe(1);
});

it('counts past empty kpi', function (): void {
    $pastDate = new DateTimeImmutable('2024-01-01');

    $emptyKpi = new Kpi(
        id: 1,
        billingMonth: $pastDate,
        type: KpiType::MONTHLY,
        value: null,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Grace',
        hasUserpic: false,
        kpi: [$emptyKpi],
        positions: [$position],
    );

    $result = $employee->toArray();

    expect($result['countPastEmptyKpi'])->toBeGreaterThan(0);
});

it('counts past filled and not sent kpi', function (): void {
    $pastDate = new DateTimeImmutable('2024-01-01');

    $filledNotSent = new Kpi(
        id: 1,
        billingMonth: $pastDate,
        type: KpiType::MONTHLY,
        value: 75,
        isSent: false,
    );

    $position = new KpiDepartmentState(
        id: 1,
        name: 'Manager',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Henry',
        hasUserpic: false,
        kpi: [$filledNotSent],
        positions: [$position],
    );

    $result = $employee->toArray();

    expect($result['countPastIsFilledAndNotSentKpi'])->toBeGreaterThan(0);
});

it('returns main position info', function (): void {
    $position = new KpiDepartmentState(
        id: 1,
        name: 'Senior Developer',
        isBoss: true,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Ivy',
        hasUserpic: false,
        kpi: [],
        positions: [$position],
    );

    $result = $employee->getMainPosition();

    expect($result['positionName'])->toBe('Senior Developer')
        ->and($result['isBoss'])->toBeTrue();
});

it('handles multiple positions and returns first', function (): void {
    $position1 = new KpiDepartmentState(
        id: 1,
        name: 'Team Lead',
        isBoss: true,
    );

    $position2 = new KpiDepartmentState(
        id: 2,
        name: 'Developer',
        isBoss: false,
    );

    $employee = new KpiEmployee(
        id: 1,
        name: 'Jack',
        hasUserpic: false,
        kpi: [],
        positions: [$position1, $position2],
    );

    $result = $employee->getMainPosition();

    expect($result['positionName'])->toBe('Team Lead')
        ->and($result['isBoss'])->toBeTrue();
});
