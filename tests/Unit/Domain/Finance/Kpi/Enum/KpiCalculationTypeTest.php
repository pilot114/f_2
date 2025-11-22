<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Enum;

use App\Domain\Finance\Kpi\Enum\KpiCalculationType;

it('has correct manual value', function (): void {
    expect(KpiCalculationType::MANUAL->value)->toBe(1);
});

it('has correct auto value', function (): void {
    expect(KpiCalculationType::AUTO->value)->toBe(2);
});

it('has all expected cases', function (): void {
    $cases = KpiCalculationType::cases();

    expect($cases)->toHaveCount(2);
});

it('can get case by value', function (): void {
    expect(KpiCalculationType::from(1))->toBe(KpiCalculationType::MANUAL)
        ->and(KpiCalculationType::from(2))->toBe(KpiCalculationType::AUTO);
});

it('returns correct manual title', function (): void {
    expect(KpiCalculationType::MANUAL->getTitle())->toBe('ручной расчёт');
});

it('returns correct auto title', function (): void {
    expect(KpiCalculationType::AUTO->getTitle())->toBe('автоматический расчёт');
});
