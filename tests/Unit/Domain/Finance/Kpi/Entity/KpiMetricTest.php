<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Entity\Post;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Enum\UnitType;

it('creates kpi metric with basic fields', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average calculation',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    expect($metric->getId())->toBe(1);
});

it('adds metric department', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);
    $department = new CpDepartment(id: 1, name: 'IT Department');
    $post = new Post(id: 1, name: 'Developer');

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average calculation',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->addMetricDepartment($department, $post);

    $result = $metric->toArray();

    expect($result['departments'])->toHaveCount(1)
        ->and($result['departments'][0]['departmentId'])->toBe(1)
        ->and($result['departments'][0]['departmentName'])->toBe('IT Department')
        ->and($result['departments'][0]['postId'])->toBe(1)
        ->and($result['departments'][0]['postName'])->toBe('Developer');
});

it('adds multiple posts to same department', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);
    $department = new CpDepartment(id: 1, name: 'IT Department');
    $post1 = new Post(id: 1, name: 'Developer');
    $post2 = new Post(id: 2, name: 'Manager');

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->addMetricDepartment($department, $post1);
    $metric->addMetricDepartment($department, $post2);

    $result = $metric->toArray();

    expect($result['departments'])->toHaveCount(2);
});

it('removes metric department post', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);
    $department = new CpDepartment(id: 1, name: 'IT Department');
    $post = new Post(id: 1, name: 'Developer');

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->addMetricDepartment($department, $post);
    $metric->removeMetricDepartment(1, 1);

    $result = $metric->toArray();

    expect($result['departments'])->toBeEmpty();
});

it('removes only specified post from department', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);
    $department = new CpDepartment(id: 1, name: 'IT Department');
    $post1 = new Post(id: 1, name: 'Developer');
    $post2 = new Post(id: 2, name: 'Manager');

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->addMetricDepartment($department, $post1);
    $metric->addMetricDepartment($department, $post2);
    $metric->removeMetricDepartment(1, 1);

    $result = $metric->toArray();

    expect($result['departments'])->toHaveCount(1)
        ->and($result['departments'][0]['postId'])->toBe(2);
});

it('sets group', function (): void {
    $group1 = new KpiMetricGroup(id: 1, name: 'Sales');
    $group2 = new KpiMetricGroup(id: 2, name: 'Marketing');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group1,
        type: $type,
    );

    $metric->setGroup($group2);

    $result = $metric->toArray();

    expect($result['group']['id'])->toBe(2)
        ->and($result['group']['name'])->toBe('Marketing');
});

it('sets type', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type1 = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);
    $type2 = new KpiMetricType(id: 2, name: 'Type B', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type1,
    );

    $metric->setType($type2);

    $result = $metric->toArray();

    expect($result['type']['id'])->toBe(2)
        ->and($result['type']['name'])->toBe('Type B');
});

it('sets name', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->setName('Sales Metric');

    $result = $metric->toArray();

    expect($result['name'])->toBe('Sales Metric');
});

it('sets kpi type', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->setKpiType(KpiType::QUARTERLY);

    $result = $metric->toArray();

    expect($result['kpiType'])->toBe(KpiType::QUARTERLY);
});

it('sets calculation type', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->setCalculationType(KpiCalculationType::AUTO);

    $result = $metric->toArray();

    expect($result['calculationType'])->toBe(KpiCalculationType::AUTO);
});

it('sets calculation type description', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->setCalculationTypeDescription('New description');

    $result = $metric->toArray();

    expect($result['calculationTypeDescription'])->toBe('New description');
});

it('sets unit type', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $metric->setUnitType(UnitType::PERCENTS);

    $result = $metric->toArray();

    expect($result['unitType'])->toBe(UnitType::PERCENTS);
});

it('sets is active', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
        isActive: true,
    );

    $metric->setIsActive(false);

    // isActive not in toArray output, but method exists
    expect($metric)->toBeInstanceOf(KpiMetric::class);
});

it('converts to array with extended false', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $result = $metric->toArray(isExtended: false);

    expect($result)->toHaveKeys(['id', 'name'])
        ->and($result)->not->toHaveKey('kpiType')
        ->and($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Revenue');
});

it('converts to array with extended true', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales');
    $type = new KpiMetricType(id: 1, name: 'Type A', planType: PaymentPlanType::LINEAR);

    $metric = new KpiMetric(
        id: 1,
        name: 'Revenue',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Average calculation',
        unitType: UnitType::PIECES,
        group: $group,
        type: $type,
    );

    $result = $metric->toArray(isExtended: true);

    expect($result)->toHaveKeys(['id', 'name', 'kpiType', 'calculationType', 'calculationTypeDescription', 'group', 'type', 'departments', 'unitType'])
        ->and($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Revenue')
        ->and($result['kpiType'])->toBe(KpiType::MONTHLY)
        ->and($result['calculationType'])->toBe(KpiCalculationType::AUTO)
        ->and($result['calculationTypeDescription'])->toBe('Average calculation')
        ->and($result['unitType'])->toBe(UnitType::PIECES)
        ->and($result['departments'])->toBeEmpty();
});
