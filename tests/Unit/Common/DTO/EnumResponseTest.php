<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Common\Dictionary\DTO;

use App\Domain\Portal\Dictionary\DTO\EnumCaseResponse;
use App\Domain\Portal\Dictionary\DTO\EnumResponse;

it('creates enum response with all fields', function (): void {
    $case1 = new EnumCaseResponse('ACTIVE', 1, 'Active');
    $case2 = new EnumCaseResponse('INACTIVE', 0, 'Inactive');

    $response = new EnumResponse(
        domain: 'User',
        name: 'UserStatus',
        cases: [$case1, $case2],
    );

    expect($response->domain)->toBe('User')
        ->and($response->name)->toBe('UserStatus')
        ->and($response->cases)->toHaveCount(2);
});

it('creates enum response with empty cases', function (): void {
    $response = new EnumResponse(
        domain: 'Finance',
        name: 'PaymentType',
        cases: [],
    );

    expect($response->cases)->toBeEmpty();
});

it('creates enum response with single case', function (): void {
    $case = new EnumCaseResponse('ENABLED', 1);

    $response = new EnumResponse(
        domain: 'Settings',
        name: 'FeatureFlag',
        cases: [$case],
    );

    expect($response->cases)->toHaveCount(1);
});

it('handles cyrillic in domain and name', function (): void {
    $response = new EnumResponse(
        domain: 'Пользователь',
        name: 'СтатусПользователя',
        cases: [],
    );

    expect($response->domain)->toBe('Пользователь')
        ->and($response->name)->toBe('СтатусПользователя');
});

it('preserves case order', function (): void {
    $case1 = new EnumCaseResponse('FIRST', 1);
    $case2 = new EnumCaseResponse('SECOND', 2);
    $case3 = new EnumCaseResponse('THIRD', 3);

    $response = new EnumResponse(
        domain: 'Test',
        name: 'Order',
        cases: [$case1, $case2, $case3],
    );

    expect($response->cases[0]->name)->toBe('FIRST')
        ->and($response->cases[1]->name)->toBe('SECOND')
        ->and($response->cases[2]->name)->toBe('THIRD');
});

it('handles special characters in name', function (): void {
    $response = new EnumResponse(
        domain: 'Domain_Test',
        name: 'Enum_Name_123',
        cases: [],
    );

    expect($response->domain)->toBe('Domain_Test')
        ->and($response->name)->toBe('Enum_Name_123');
});
