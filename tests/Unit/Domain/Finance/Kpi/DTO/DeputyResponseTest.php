<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\DTO\DeputyResponse;
use DateTimeImmutable;

it('creates deputy response with all fields', function (): void {
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('2024-12-31');
    $deputyUser = [
        'id'   => 1,
        'name' => 'John Doe',
    ];

    $response = new DeputyResponse(
        id: 1,
        dateStart: $start,
        dateEnd: $end,
        deputyUser: $deputyUser,
    );

    expect($response->id)->toBe(1)
        ->and($response->dateStart)->toBe($start)
        ->and($response->dateEnd)->toBe($end)
        ->and($response->deputyUser)->toBe($deputyUser);
});

it('handles different date ranges', function (): void {
    $start = new DateTimeImmutable('2024-06-01');
    $end = new DateTimeImmutable('2024-06-30');

    $response = new DeputyResponse(
        id: 1,
        dateStart: $start,
        dateEnd: $end,
        deputyUser: [],
    );

    expect($response->dateStart)->toBe($start)
        ->and($response->dateEnd)->toBe($end);
});

it('handles same start and end dates', function (): void {
    $date = new DateTimeImmutable('2024-01-01');

    $response = new DeputyResponse(
        id: 1,
        dateStart: $date,
        dateEnd: $date,
        deputyUser: [],
    );

    expect($response->dateStart)->toBe($date)
        ->and($response->dateEnd)->toBe($date);
});

it('handles empty deputy user array', function (): void {
    $response = new DeputyResponse(
        id: 1,
        dateStart: new DateTimeImmutable(),
        dateEnd: new DateTimeImmutable(),
        deputyUser: [],
    );

    expect($response->deputyUser)->toBeEmpty();
});

it('handles deputy user with multiple fields', function (): void {
    $deputyUser = [
        'id'       => 42,
        'name'     => 'Jane Smith',
        'email'    => 'jane@example.com',
        'position' => 'Deputy Manager',
    ];

    $response = new DeputyResponse(
        id: 1,
        dateStart: new DateTimeImmutable(),
        dateEnd: new DateTimeImmutable(),
        deputyUser: $deputyUser,
    );

    expect($response->deputyUser['id'])->toBe(42)
        ->and($response->deputyUser['name'])->toBe('Jane Smith')
        ->and($response->deputyUser['email'])->toBe('jane@example.com');
});

it('handles cyrillic in deputy user name', function (): void {
    $deputyUser = [
        'id'   => 1,
        'name' => 'Петр Петров',
    ];

    $response = new DeputyResponse(
        id: 1,
        dateStart: new DateTimeImmutable(),
        dateEnd: new DateTimeImmutable(),
        deputyUser: $deputyUser,
    );

    expect($response->deputyUser['name'])->toBe('Петр Петров');
});

it('handles different ids', function (): void {
    $response = new DeputyResponse(
        id: 999,
        dateStart: new DateTimeImmutable(),
        dateEnd: new DateTimeImmutable(),
        deputyUser: [],
    );

    expect($response->id)->toBe(999);
});
