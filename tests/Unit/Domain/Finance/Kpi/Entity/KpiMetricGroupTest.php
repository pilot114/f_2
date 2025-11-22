<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;

it('creates kpi metric group with id and name', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Sales Group');

    $result = $group->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Sales Group');
});

it('converts to array', function (): void {
    $group = new KpiMetricGroup(id: 10, name: 'Marketing Group');

    $result = $group->toArray();

    expect($result)->toBe([
        'id'   => 10,
        'name' => 'Marketing Group',
    ]);
});

it('handles cyrillic characters in name', function (): void {
    $group = new KpiMetricGroup(id: 5, name: 'Группа метрик продаж');

    $result = $group->toArray();

    expect($result['name'])->toBe('Группа метрик продаж');
});

it('handles different group names', function (string $groupName): void {
    $group = new KpiMetricGroup(id: 1, name: $groupName);

    $result = $group->toArray();

    expect($result['name'])->toBe($groupName);
})->with([
    'Sales Metrics',
    'Performance Indicators',
    'Quality Metrics',
    'Customer Satisfaction',
]);

it('toArray contains all required fields', function (): void {
    $group = new KpiMetricGroup(id: 1, name: 'Test');

    $result = $group->toArray();

    expect($result)->toHaveKeys(['id', 'name'])
        ->and($result)->toHaveCount(2);
});

it('handles empty name', function (): void {
    $group = new KpiMetricGroup(id: 1, name: '');

    $result = $group->toArray();

    expect($result['name'])->toBe('');
});
