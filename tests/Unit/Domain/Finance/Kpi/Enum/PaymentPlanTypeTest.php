<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Enum;

use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

it('has correct linear value', function (): void {
    expect(PaymentPlanType::LINEAR->value)->toBe(1);
});

it('has correct ranges value', function (): void {
    expect(PaymentPlanType::RANGES->value)->toBe(2);
});

it('has all expected cases', function (): void {
    $cases = PaymentPlanType::cases();

    expect($cases)->toHaveCount(2);
});

it('can get case by value', function (): void {
    expect(PaymentPlanType::from(1))->toBe(PaymentPlanType::LINEAR)
        ->and(PaymentPlanType::from(2))->toBe(PaymentPlanType::RANGES);
});

it('returns correct linear title', function (): void {
    expect(PaymentPlanType::LINEAR->getTitle())->toBe('линейная зависимость');
});

it('returns correct ranges title', function (): void {
    expect(PaymentPlanType::RANGES->getTitle())->toBe('деление на диапазоны');
});
