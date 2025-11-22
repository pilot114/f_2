<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\KpiMetricRange;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

it('creates kpi metric type with linear plan', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Sales Metric Type',
        planType: PaymentPlanType::LINEAR,
    );

    $result = $type->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Sales Metric Type')
        ->and($result['planType'])->toBe(PaymentPlanType::LINEAR)
        ->and($result['ranges'])->toBeEmpty();
});

it('creates kpi metric type with ranges plan', function (): void {
    $range1 = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 50,
        kpiPercent: 10,
        metricTypeId: 1,
    );

    $range2 = new KpiMetricRange(
        id: 2,
        startPercent: 50,
        endPercent: 100,
        kpiPercent: 20,
        metricTypeId: 1,
    );

    $type = new KpiMetricType(
        id: 1,
        name: 'Performance Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [$range1, $range2],
    );

    $result = $type->toArray();

    expect($result['planType'])->toBe(PaymentPlanType::RANGES)
        ->and($result['ranges'])->toHaveCount(2)
        ->and($result['ranges'][0])->toHaveKey('title')
        ->and($result['ranges'][0]['startPercent'])->toBe(0)
        ->and($result['ranges'][1]['startPercent'])->toBe(50);
});

it('sets name using setter', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Old Name',
        planType: PaymentPlanType::LINEAR,
    );

    $result = $type->setName('New Name');

    expect($result)->toBe($type)
        ->and($type->toArray()['name'])->toBe('New Name');
});

it('sets is active using setter', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
        isActive: true,
    );

    $result = $type->setIsActive(false);

    expect($result)->toBe($type);
});

it('sets plan type using setter', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
    );

    $result = $type->setPlanType(PaymentPlanType::RANGES);

    expect($result)->toBe($type)
        ->and($type->toArray()['planType'])->toBe(PaymentPlanType::RANGES);
});

it('sets ranges using setter', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::RANGES,
    );

    $range = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 100,
        kpiPercent: 50,
        metricTypeId: 1,
    );

    $result = $type->setRanges([$range]);

    expect($result)->toBe($type)
        ->and($type->toArray()['ranges'])->toHaveCount(1);
});

it('returns id', function (): void {
    $type = new KpiMetricType(
        id: 42,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
    );

    expect($type->getId())->toBe(42);
});

it('ranges include title when plan type is ranges', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 50,
        endPercent: 80,
        kpiPercent: 25,
        metricTypeId: 1,
    );

    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::RANGES,
        ranges: [$range],
    );

    $result = $type->toArray();

    expect($result['ranges'][0]['title'])->toContain('50%')
        ->and($result['ranges'][0]['title'])->toContain('80%')
        ->and($result['ranges'][0]['title'])->toContain('25%');
});

it('returns empty metrics when no metrics provided', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
    );

    $result = $type->toArray();

    expect($result['metrics'])->toBeEmpty();
});

it('toArray contains all required fields', function (): void {
    $type = new KpiMetricType(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
    );

    $result = $type->toArray();

    expect($result)->toHaveKeys(['id', 'name', 'planType', 'ranges', 'metrics']);
});
