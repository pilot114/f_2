<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Enum;

use App\Domain\CallCenter\UseDesk\Enum\UserType;

it('has correct enum values', function (): void {
    expect(UserType::CLIENT->value)->toBe('client')
        ->and(UserType::EMPLOYEE->value)->toBe('user');
});

it('can create from string value', function (): void {
    expect(UserType::tryFrom('client'))->toBe(UserType::CLIENT)
        ->and(UserType::tryFrom('user'))->toBe(UserType::EMPLOYEE)
        ->and(UserType::tryFrom('invalid'))->toBeNull();
});
