<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\FilterOption;

it('has correct enum cases', function (): void {
    expect(FilterOption::Q_ANY)->toBe(FilterOption::Q_ANY);
    expect(FilterOption::Q_SOME)->toBe(FilterOption::Q_SOME);
    expect(FilterOption::Q_NONE)->toBe(FilterOption::Q_NONE);
});

it('has correct string values', function (): void {
    expect(FilterOption::Q_ANY->value)->toBe('Q_ANY');
    expect(FilterOption::Q_SOME->value)->toBe('Q_SOME');
    expect(FilterOption::Q_NONE->value)->toBe('Q_NONE');
});

it('returns all cases', function (): void {
    $cases = FilterOption::cases();

    expect($cases)->toHaveCount(3);
    expect($cases)->toContain(FilterOption::Q_ANY);
    expect($cases)->toContain(FilterOption::Q_SOME);
    expect($cases)->toContain(FilterOption::Q_NONE);
});

it('returns all values', function (): void {
    $values = FilterOption::values();

    expect($values)->toBe(['Q_ANY', 'Q_SOME', 'Q_NONE']);
    expect($values)->toHaveCount(3);
});

it('can be created from string value', function (): void {
    $enum1 = FilterOption::from('Q_ANY');
    $enum2 = FilterOption::from('Q_SOME');
    $enum3 = FilterOption::from('Q_NONE');

    expect($enum1)->toBe(FilterOption::Q_ANY);
    expect($enum2)->toBe(FilterOption::Q_SOME);
    expect($enum3)->toBe(FilterOption::Q_NONE);
});

it('can try to create from string value', function (): void {
    $validEnum = FilterOption::tryFrom('Q_ANY');
    $invalidEnum = FilterOption::tryFrom('INVALID');

    expect($validEnum)->toBe(FilterOption::Q_ANY);
    expect($invalidEnum)->toBeNull();
});
