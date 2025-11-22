<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\DTO\KpiResponsibleResponse;

it('creates kpi responsible response with all fields', function (): void {
    $user = [
        'id'   => 1,
        'name' => 'John Doe',
    ];
    $enterprise = [
        'id'   => 10,
        'name' => 'Tech Corp',
    ];

    $response = new KpiResponsibleResponse(
        id: 1,
        user: $user,
        enterprise: $enterprise,
    );

    expect($response->id)->toBe(1)
        ->and($response->user)->toBe($user)
        ->and($response->enterprise)->toBe($enterprise);
});

it('handles empty user array', function (): void {
    $response = new KpiResponsibleResponse(
        id: 1,
        user: [],
        enterprise: [
            'id' => 1,
        ],
    );

    expect($response->user)->toBeEmpty();
});

it('handles empty enterprise array', function (): void {
    $response = new KpiResponsibleResponse(
        id: 1,
        user: [
            'id' => 1,
        ],
        enterprise: [],
    );

    expect($response->enterprise)->toBeEmpty();
});

it('handles user with multiple fields', function (): void {
    $user = [
        'id'       => 1,
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'position' => 'Manager',
    ];

    $response = new KpiResponsibleResponse(
        id: 1,
        user: $user,
        enterprise: [],
    );

    expect($response->user['name'])->toBe('John Doe')
        ->and($response->user['email'])->toBe('john@example.com');
});

it('handles enterprise with multiple fields', function (): void {
    $enterprise = [
        'id'   => 10,
        'name' => 'Tech Corp',
        'code' => 'TC001',
    ];

    $response = new KpiResponsibleResponse(
        id: 1,
        user: [],
        enterprise: $enterprise,
    );

    expect($response->enterprise['name'])->toBe('Tech Corp')
        ->and($response->enterprise['code'])->toBe('TC001');
});

it('handles cyrillic in user name', function (): void {
    $user = [
        'id'   => 1,
        'name' => 'Иван Иванов',
    ];

    $response = new KpiResponsibleResponse(
        id: 1,
        user: $user,
        enterprise: [],
    );

    expect($response->user['name'])->toBe('Иван Иванов');
});

it('handles different id types', function (): void {
    $response = new KpiResponsibleResponse(
        id: 999,
        user: [],
        enterprise: [],
    );

    expect($response->id)->toBe(999);
});
