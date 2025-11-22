<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Enum;

use App\Domain\Finance\Kpi\Enum\UnitType;

it('has correct pieces value', function (): void {
    expect(UnitType::PIECES->value)->toBe(1);
});

it('has correct percents value', function (): void {
    expect(UnitType::PERCENTS->value)->toBe(2);
});

it('has correct conditional units value', function (): void {
    expect(UnitType::CONDITIONAL_UNITS->value)->toBe(3);
});

it('has all expected cases', function (): void {
    $cases = UnitType::cases();

    expect($cases)->toHaveCount(3);
});

it('can get case by value', function (): void {
    expect(UnitType::from(1))->toBe(UnitType::PIECES)
        ->and(UnitType::from(2))->toBe(UnitType::PERCENTS)
        ->and(UnitType::from(3))->toBe(UnitType::CONDITIONAL_UNITS);
});

it('returns correct pieces title', function (): void {
    expect(UnitType::PIECES->getTitle())->toBe('штуки');
});

it('returns correct percents title', function (): void {
    expect(UnitType::PERCENTS->getTitle())->toBe('%');
});

it('returns correct conditional units title', function (): void {
    expect(UnitType::CONDITIONAL_UNITS->getTitle())->toBe('усл. ед.');
});
