<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\FindItemResponse;

it('creates readonly find item response with valid data', function (): void {
    $response = new FindItemResponse(1, 'Test Item');

    expect($response->id)->toBe(1);
    expect($response->name)->toBe('Test Item');
});

it('creates find item response with zero id', function (): void {
    $response = new FindItemResponse(0, 'Zero ID Item');

    expect($response->id)->toBe(0);
    expect($response->name)->toBe('Zero ID Item');
});

it('creates find item response with empty name', function (): void {
    $response = new FindItemResponse(42, '');

    expect($response->id)->toBe(42);
    expect($response->name)->toBe('');
});

it('creates find item response with special characters in name', function (): void {
    $response = new FindItemResponse(1, 'Элемент с русскими символами & спецсимволы!@#$');

    expect($response->id)->toBe(1);
    expect($response->name)->toBe('Элемент с русскими символами & спецсимволы!@#$');
});

it('creates find item response with long name', function (): void {
    $longName = str_repeat('A', 1000);
    $response = new FindItemResponse(999, $longName);

    expect($response->id)->toBe(999);
    expect($response->name)->toBe($longName);
    expect(strlen($response->name))->toBe(1000);
});

it('has public properties that can be accessed', function (): void {
    $response = new FindItemResponse(1, 'Original Name');

    // Verify properties are accessible
    expect($response->id)->toBe(1);
    expect($response->name)->toBe('Original Name');
});
