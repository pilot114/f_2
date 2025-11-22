<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Events\Rewards\Enum;

use App\Domain\Events\Rewards\Enum\GroupType;

it('has correct group value', function (): void {
    expect(GroupType::GROUP->value)->toBe(1);
});

it('has correct category value', function (): void {
    expect(GroupType::CATEGORY->value)->toBe(2);
});

it('has all expected cases', function (): void {
    $cases = GroupType::cases();

    expect($cases)->toHaveCount(2);
});

it('can get case by value', function (): void {
    expect(GroupType::from(1))->toBe(GroupType::GROUP)
        ->and(GroupType::from(2))->toBe(GroupType::CATEGORY);
});
