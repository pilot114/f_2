<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Enum;

use App\Domain\CallCenter\UseDesk\Enum\StatusType;

it('has correct enum values', function (): void {
    expect(StatusType::NEW->value)->toBe('new')
        ->and(StatusType::REOPENED->value)->toBe('reopened')
        ->and(StatusType::CLOSED->value)->toBe('closed');
});

it('can create from string value', function (): void {
    expect(StatusType::tryFrom('new'))->toBe(StatusType::NEW)
        ->and(StatusType::tryFrom('reopened'))->toBe(StatusType::REOPENED)
        ->and(StatusType::tryFrom('closed'))->toBe(StatusType::CLOSED)
        ->and(StatusType::tryFrom('invalid'))->toBeNull();
});
