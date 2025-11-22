<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Enum;

use App\Domain\Finance\Kpi\Enum\KpiType;

it('has correct monthly value', function (): void {
    expect(KpiType::MONTHLY->value)->toBe(1);
});

it('has correct bimonthly value', function (): void {
    expect(KpiType::BIMONTHLY->value)->toBe(2);
});

it('has correct quarterly value', function (): void {
    expect(KpiType::QUARTERLY->value)->toBe(3);
});

it('has all expected cases', function (): void {
    $cases = KpiType::cases();

    expect($cases)->toHaveCount(3);
});

it('can get case by value', function (): void {
    expect(KpiType::from(1))->toBe(KpiType::MONTHLY)
        ->and(KpiType::from(2))->toBe(KpiType::BIMONTHLY)
        ->and(KpiType::from(3))->toBe(KpiType::QUARTERLY);
});

it('returns correct monthly title', function (): void {
    expect(KpiType::MONTHLY->getTitle())->toBe('KPI ежемесячный');
});

it('returns correct bimonthly title', function (): void {
    expect(KpiType::BIMONTHLY->getTitle())->toBe('KPI спринт (двухмесячный)');
});

it('returns correct quarterly title', function (): void {
    expect(KpiType::QUARTERLY->getTitle())->toBe('KPI квартальный');
});
