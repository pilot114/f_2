<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\PointsLoan\Entity;

use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use DateTimeImmutable;

it('creates guarantor with all required fields', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'CONTRACT-123',
    );

    expect($guarantor->id)->toBe(1)
        ->and($guarantor->contract)->toBe('CONTRACT-123')
        ->and($guarantor->closedAt)->toBeNull();
});

it('creates guarantor with closed date', function (): void {
    $closedDate = new DateTimeImmutable('2024-01-15');

    $guarantor = new Guarantor(
        id: 2,
        contract: 'CONTRACT-456',
        closedAt: $closedDate,
    );

    expect($guarantor->id)->toBe(2)
        ->and($guarantor->contract)->toBe('CONTRACT-456')
        ->and($guarantor->closedAt)->toBe($closedDate);
});

it('returns true for active guarantor when closedAt is null', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'CONTRACT-789',
    );

    expect($guarantor->isActive())->toBeTrue();
});

it('returns false for inactive guarantor when closedAt is set', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'CONTRACT-999',
        closedAt: new DateTimeImmutable('2024-01-01'),
    );

    expect($guarantor->isActive())->toBeFalse();
});

it('handles different contract numbers', function (string $contract): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: $contract,
    );

    expect($guarantor->contract)->toBe($contract);
})->with([
    'CONTRACT-001',
    'ABC-123-XYZ',
    'Договор №12345',
    '2024/01/CONTRACT',
]);

it('handles different ids', function (int $id): void {
    $guarantor = new Guarantor(
        id: $id,
        contract: 'TEST',
    );

    expect($guarantor->id)->toBe($id);
})->with([1, 100, 999, 123456]);

it('is readonly class', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'TEST',
    );

    expect($guarantor)->toBeInstanceOf(Guarantor::class);
});

it('handles recent closed date', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'CONTRACT-RECENT',
        closedAt: new DateTimeImmutable('now'),
    );

    expect($guarantor->isActive())->toBeFalse();
});

it('handles past closed date', function (): void {
    $guarantor = new Guarantor(
        id: 1,
        contract: 'CONTRACT-PAST',
        closedAt: new DateTimeImmutable('2000-01-01'),
    );

    expect($guarantor->isActive())->toBeFalse();
});
