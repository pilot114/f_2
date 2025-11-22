<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Common\Dictionary\DTO;

use App\Domain\Portal\Dictionary\DTO\EnumCaseResponse;

it('creates enum case response with all fields', function (): void {
    $response = new EnumCaseResponse(
        name: 'ACTIVE',
        value: 1,
        title: 'Active Status',
    );

    expect($response->name)->toBe('ACTIVE')
        ->and($response->value)->toBe(1)
        ->and($response->title)->toBe('Active Status');
});

it('creates enum case response without title', function (): void {
    $response = new EnumCaseResponse(
        name: 'PENDING',
        value: 0,
    );

    expect($response->name)->toBe('PENDING')
        ->and($response->value)->toBe(0)
        ->and($response->title)->toBeNull();
});

it('handles string value', function (): void {
    $response = new EnumCaseResponse(
        name: 'SUCCESS',
        value: 'success_status',
        title: 'Success',
    );

    expect($response->value)->toBe('success_status')
        ->and($response->value)->toBeString();
});

it('handles integer value', function (): void {
    $response = new EnumCaseResponse(
        name: 'FAILED',
        value: 2,
    );

    expect($response->value)->toBe(2)
        ->and($response->value)->toBeInt();
});

it('handles cyrillic in title', function (): void {
    $response = new EnumCaseResponse(
        name: 'ACTIVE',
        value: 1,
        title: 'Активный',
    );

    expect($response->title)->toBe('Активный');
});

it('handles empty string title', function (): void {
    $response = new EnumCaseResponse(
        name: 'TEST',
        value: 1,
        title: '',
    );

    expect($response->title)->toBe('');
});

it('handles negative integer value', function (): void {
    $response = new EnumCaseResponse(
        name: 'ERROR',
        value: -1,
    );

    expect($response->value)->toBe(-1);
});
