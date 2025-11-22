<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\KpiMetricRange;

it('creates kpi metric range with all fields', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 50,
        kpiPercent: 10,
        metricTypeId: 5,
    );

    $result = $range->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['startPercent'])->toBe(0)
        ->and($result['endPercent'])->toBe(50)
        ->and($result['kpiPercent'])->toBe(10);
});

it('returns correct title', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 50,
        endPercent: 80,
        kpiPercent: 20,
        metricTypeId: 1,
    );

    expect($range->getTitle())->toBe('Выполнение плана 50%-80% KPI к выплате 20%');
});

it('converts to array', function (): void {
    $range = new KpiMetricRange(
        id: 10,
        startPercent: 80,
        endPercent: 100,
        kpiPercent: 50,
        metricTypeId: 2,
    );

    $result = $range->toArray();

    expect($result)->toBe([
        'id'           => 10,
        'startPercent' => 80,
        'endPercent'   => 100,
        'kpiPercent'   => 50,
    ]);
});

it('handles different percentage ranges', function (int $start, int $end, int $kpi): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: $start,
        endPercent: $end,
        kpiPercent: $kpi,
        metricTypeId: 1,
    );

    $result = $range->toArray();

    expect($result['startPercent'])->toBe($start)
        ->and($result['endPercent'])->toBe($end)
        ->and($result['kpiPercent'])->toBe($kpi);
})->with([
    [0, 25, 5],
    [25, 50, 10],
    [50, 75, 25],
    [75, 90, 40],
    [90, 100, 50],
]);

it('title contains all percentage values', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 60,
        endPercent: 90,
        kpiPercent: 30,
        metricTypeId: 1,
    );

    $title = $range->getTitle();

    expect($title)->toContain('60%')
        ->and($title)->toContain('90%')
        ->and($title)->toContain('30%')
        ->and($title)->toContain('Выполнение плана')
        ->and($title)->toContain('KPI к выплате');
});

it('handles zero start percent', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 100,
        kpiPercent: 100,
        metricTypeId: 1,
    );

    expect($range->toArray()['startPercent'])->toBe(0)
        ->and($range->getTitle())->toContain('0%');
});

it('toArray does not include metricTypeId', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 50,
        endPercent: 100,
        kpiPercent: 25,
        metricTypeId: 999,
    );

    $result = $range->toArray();

    expect($result)->not->toHaveKey('metricTypeId')
        ->and($result)->toHaveCount(4);
});

it('handles high kpi percent values', function (): void {
    $range = new KpiMetricRange(
        id: 1,
        startPercent: 100,
        endPercent: 120,
        kpiPercent: 150,
        metricTypeId: 1,
    );

    expect($range->toArray()['kpiPercent'])->toBe(150)
        ->and($range->getTitle())->toContain('150%');
});
