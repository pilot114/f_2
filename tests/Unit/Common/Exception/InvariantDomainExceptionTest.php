<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Exception;

use App\Common\Exception\DomainException;
use App\Common\Exception\InvariantDomainException;

it('creates InvariantDomainException with message', function (): void {
    // Arrange & Act
    $exception = new InvariantDomainException('Invalid entity state');

    // Assert
    expect($exception->getMessage())->toBe('Invalid entity state')
        ->and($exception->getCode())->toBe(DomainException::INVARIANT)
        ->and($exception->getCode())->toBe(DomainException::INVARIANT);
});

it('extends DomainException', function (): void {
    // Arrange
    $exception = new InvariantDomainException('Test error');

    // Assert
    expect($exception)->toBeInstanceOf(DomainException::class);
});

it('has correct error code', function (): void {
    // Arrange
    $exception = new InvariantDomainException('Any message');

    // Assert
    expect($exception->getCode())->toBe(601);
});

it('creates different InvariantDomainExceptions with different messages', function (string $message): void {
    // Arrange & Act
    $exception = new InvariantDomainException($message);

    // Assert
    expect($exception->getMessage())->toBe($message)
        ->and($exception->getCode())->toBe(DomainException::INVARIANT);
})->with([
    'Invalid employee status',
    'KPI metric value must be positive',
    'Start date must be before end date',
    'Entity data integrity violation',
]);

it('can be caught as DomainException', function (): void {
    // Arrange
    $caught = false;

    try {
        throw new InvariantDomainException('Test');
    } catch (DomainException $e) {
        $caught = true;
    }

    // Assert
    expect($caught)->toBeTrue();
});

it('has empty context by default', function (): void {
    // Arrange
    $exception = new InvariantDomainException('Test');

    // Assert
    expect($exception->getContext())->toBe([]);
});

it('is used for entity validation errors', function (): void {
    // This test documents the intended use case
    // Arrange & Act
    $exception = new InvariantDomainException('Entity invariant violated: age cannot be negative');

    // Assert
    expect($exception)->toBeInstanceOf(DomainException::class)
        ->and($exception->getMessage())->toContain('invariant violated');
});
