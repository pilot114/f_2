<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\PointsLoan\Enum;

use App\Domain\Finance\PointsLoan\Enum\LoanStatus;

it('has correct paid value', function (): void {
    expect(LoanStatus::PAID->value)->toBe(1);
});

it('has correct not paid value', function (): void {
    expect(LoanStatus::NOT_PAID->value)->toBe(2);
});

it('has all expected cases', function (): void {
    $cases = LoanStatus::cases();

    expect($cases)->toHaveCount(2);
});

it('can get case by value', function (): void {
    expect(LoanStatus::from(1))->toBe(LoanStatus::PAID)
        ->and(LoanStatus::from(2))->toBe(LoanStatus::NOT_PAID);
});

it('returns correct paid title', function (): void {
    expect(LoanStatus::PAID->getTitle())->toBe('Погашен');
});

it('returns correct not paid title', function (): void {
    expect(LoanStatus::NOT_PAID->getTitle())->toBe('Не погашен');
});
