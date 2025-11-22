<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Violation;
use App\Domain\Finance\PointsLoan\Enum\ViolationType;

it('creates violation with correct properties', function (): void {
    // Arrange & Act
    $violation = new Violation(1, ViolationType::UNDER_CONTROL, 'Late payment');

    // Assert
    expect($violation->id)->toBe(1);
    expect($violation->type)->toBe(ViolationType::UNDER_CONTROL);
    expect($violation->commentary)->toBe('Late payment');
});

it('creates violation with different values', function (): void {
    // Arrange & Act
    $violation = new Violation(999, ViolationType::DO_NOT_ISSUE, 'Overdue loan');

    // Assert
    expect($violation->id)->toBe(999);
    expect($violation->type)->toBe(ViolationType::DO_NOT_ISSUE);
    expect($violation->commentary)->toBe('Overdue loan');
});

it('violation properties are readonly', function (): void {
    // Arrange
    $violation = new Violation(1, ViolationType::UNDER_CONTROL, 'Test violation');

    // Assert - свойства readonly, нельзя изменить
    expect($violation)->toBeInstanceOf(Violation::class);
    expect($violation->id)->toBe(1);
    expect($violation->type)->toBe(ViolationType::UNDER_CONTROL);
    expect($violation->commentary)->toBe('Test violation');
});

it('returns correct array representation', function (): void {
    // Arrange
    $violation = new Violation(1, ViolationType::DO_NOT_ISSUE, 'Test commentary');

    // Act
    $array = $violation->toArray();

    // Assert
    expect($array)->toBe([
        'id'         => 1,
        'type'       => ViolationType::DO_NOT_ISSUE,
        'commentary' => 'Test commentary',
    ]);
});
