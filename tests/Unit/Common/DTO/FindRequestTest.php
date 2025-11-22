<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\FindRequest;

it('creates readonly find request with default values', function (): void {
    $request = new FindRequest();

    expect($request->page)->toBeNull();
    expect($request->perPage)->toBeNull();
});

it('creates find request with page only', function (): void {
    $request = new FindRequest(page: 1);

    expect($request->page)->toBe(1);
    expect($request->perPage)->toBeNull();
});

it('creates find request with per page only', function (): void {
    $request = new FindRequest(perPage: 20);

    expect($request->page)->toBeNull();
    expect($request->perPage)->toBe(20);
});

it('creates find request with both parameters', function (): void {
    $request = new FindRequest(page: 3, perPage: 50);

    expect($request->page)->toBe(3);
    expect($request->perPage)->toBe(50);
});

it('creates find request with zero values', function (): void {
    $request = new FindRequest(page: 0, perPage: 0);

    expect($request->page)->toBe(0);
    expect($request->perPage)->toBe(0);
});

it('creates find request with negative values', function (): void {
    $request = new FindRequest(page: -1, perPage: -10);

    expect($request->page)->toBe(-1);
    expect($request->perPage)->toBe(-10);
});

it('creates find request with large values', function (): void {
    $request = new FindRequest(page: 999999, perPage: 1000000);

    expect($request->page)->toBe(999999);
    expect($request->perPage)->toBe(1000000);
});

it('has public properties that can be accessed', function (): void {
    $request = new FindRequest(page: 1, perPage: 20);

    // Verify properties are accessible
    expect($request->page)->toBe(1);
    expect($request->perPage)->toBe(20);
});
