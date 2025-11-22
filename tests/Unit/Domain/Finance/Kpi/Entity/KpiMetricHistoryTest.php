<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\Kpi\Entity\KpiMetricHistory;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Enum\UnitType;

it('creates kpi metric history with all fields', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Sales Metric',
        factualValue: 100,
        planValue: 120,
        weight: 0.5,
        calculationDescription: 'Test calculation',
        rangesCount: 0,
        rangesDescription: '',
        unitType: UnitType::PIECES,
        planType: PaymentPlanType::LINEAR,
    );

    $result = $metric->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Sales Metric')
        ->and($result['factual'])->toBe(100)
        ->and($result['plan'])->toBe(120)
        ->and($result['weight'])->toBe(0.5)
        ->and($result['description'])->toBe('Test calculation')
        ->and($result['unitType'])->toBe(UnitType::PIECES)
        ->and($result['planType'])->toBe(PaymentPlanType::LINEAR);
});

it('sets data correctly', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Metric',
        factualValue: 50,
        planValue: 60,
        weight: 0.3,
        calculationDescription: 'Desc',
        rangesCount: 0,
        rangesDescription: '',
        unitType: UnitType::PERCENTS,
    );

    $metric->setData(plan: 200, factual: 180, weight: 0.8);

    $result = $metric->toArray();

    expect($result['plan'])->toBe(200)
        ->and($result['factual'])->toBe(180)
        ->and($result['weight'])->toBe(0.8);
});

it('returns empty ranges when rangesDescription is empty', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 0,
        rangesDescription: '',
        unitType: UnitType::PIECES,
    );

    $result = $metric->toArray();

    expect($result['ranges'])->toBeEmpty();
});

it('parses ranges correctly', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 2,
        rangesDescription: '0-50-10;50-100-20',
        unitType: UnitType::PIECES,
    );

    $result = $metric->toArray();

    expect($result['ranges'])->toHaveCount(2)
        ->and($result['ranges'][0])->toBe([
            'startPercent' => 0,
            'endPercent'   => 50,
            'kpiPercent'   => 10,
        ])
        ->and($result['ranges'][1])->toBe([
            'startPercent' => 50,
            'endPercent'   => 100,
            'kpiPercent'   => 20,
        ]);
});

it('parses single range requires semicolon', function (): void {
    // Note: Code requires semicolon separator, single range without semicolon returns empty array
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 0,
        rangesDescription: '0-100-50',
        unitType: UnitType::PERCENTS,
    );

    $result = $metric->toArray();

    // Without semicolon, getRanges returns empty array (see str_contains check in code)
    expect($result['ranges'])->toBeEmpty();
});

it('throws exception when ranges count mismatch', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 3,
        rangesDescription: '0-50-10;50-100-20',
        unitType: UnitType::PIECES,
    );

    expect(fn (): array => $metric->toArray())->toThrow(InvariantDomainException::class);
});

it('throws exception when range format is invalid', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 2,
        rangesDescription: '0-50;100',
        unitType: UnitType::PIECES,
    );

    expect(fn (): array => $metric->toArray())->toThrow(InvariantDomainException::class);
});

it('handles null plan type', function (): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 0,
        rangesDescription: '',
        unitType: UnitType::PIECES,
        planType: null,
    );

    $result = $metric->toArray();

    expect($result['planType'])->toBeNull();
});

it('handles different unit types', function (UnitType $unitType): void {
    $metric = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 100,
        weight: 1.0,
        calculationDescription: 'Test',
        rangesCount: 0,
        rangesDescription: '',
        unitType: $unitType,
    );

    $result = $metric->toArray();

    expect($result['unitType'])->toBe($unitType);
})->with([
    UnitType::PIECES,
    UnitType::PERCENTS,
    UnitType::CONDITIONAL_UNITS,
]);
