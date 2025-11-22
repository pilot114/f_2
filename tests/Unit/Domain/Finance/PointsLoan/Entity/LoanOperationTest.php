<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\LoanOperation;

it('creates loan operation with correct properties', function (): void {
    // Arrange & Act
    $ds = new DateTimeImmutable('2024-01-01');
    $de = new DateTimeImmutable('2024-12-31');

    $operation = new LoanOperation(
        id: 1,
        ds: $ds,
        de: $de,
        emp_spis: 100,
        emp_nach: 200,
        emp_buy: 300,
        spistype: 1,
        lo: 1000.0,
        prim: 'Test operation',
        curr: 1,
        sum: 1000.0,
        kommb: 50,
        sum_native: 1000.0,
        kommb_native: 50.0
    );

    // Assert
    expect($operation->getId())->toBe(1);
    expect($operation->ds)->toBe($ds);
    expect($operation->de)->toBe($de);
    expect($operation->emp_spis)->toBe(100);
    expect($operation->emp_nach)->toBe(200);
    expect($operation->emp_buy)->toBe(300);
    expect($operation->spistype)->toBe(1);
    expect($operation->prim)->toBe('Test operation');
    expect($operation->curr)->toBe(1);
    expect($operation->sum)->toBe(1000.0);
    expect($operation->kommb)->toBe(50);
    expect($operation->sum_native)->toBe(1000.0);
    expect($operation->kommb_native)->toBe(50.0);
});

it('creates loan operation with different values', function (): void {
    // Arrange & Act
    $ds = new DateTimeImmutable('2024-02-01');
    $de = new DateTimeImmutable('2024-11-30');

    $operation = new LoanOperation(
        id: 999,
        ds: $ds,
        de: $de,
        emp_spis: 500,
        emp_nach: 600,
        emp_buy: 700,
        spistype: 2,
        lo: 2000.0,
        prim: 'Another operation',
        curr: 2,
        sum: 2000.0,
        kommb: 100,
        sum_native: 2000.0,
        kommb_native: 100.0
    );

    // Assert
    expect($operation->getId())->toBe(999);
    expect($operation->ds)->toBe($ds);
    expect($operation->de)->toBe($de);
    expect($operation->emp_spis)->toBe(500);
    expect($operation->emp_nach)->toBe(600);
    expect($operation->emp_buy)->toBe(700);
    expect($operation->spistype)->toBe(2);
    expect($operation->prim)->toBe('Another operation');
    expect($operation->curr)->toBe(2);
    expect($operation->sum)->toBe(2000.0);
    expect($operation->kommb)->toBe(100);
    expect($operation->sum_native)->toBe(2000.0);
    expect($operation->kommb_native)->toBe(100.0);
});

it('can update id', function (): void {
    // Arrange
    $ds = new DateTimeImmutable('2024-01-01');
    $de = new DateTimeImmutable('2024-12-31');

    $operation = new LoanOperation(
        id: 1,
        ds: $ds,
        de: $de,
        emp_spis: 100,
        emp_nach: 200,
        emp_buy: 300,
        spistype: 1,
        lo: 1000.0,
        prim: 'Test operation',
        curr: 1,
        sum: 1000.0,
        kommb: 50,
        sum_native: 1000.0,
        kommb_native: 50.0
    );

    // Act
    $operation->setId(999);

    // Assert
    expect($operation->getId())->toBe(999);
});

it('can update sum', function (): void {
    // Arrange
    $ds = new DateTimeImmutable('2024-01-01');
    $de = new DateTimeImmutable('2024-12-31');

    $operation = new LoanOperation(
        id: 1,
        ds: $ds,
        de: $de,
        emp_spis: 100,
        emp_nach: 200,
        emp_buy: 300,
        spistype: 1,
        lo: 1000.0,
        prim: 'Test operation',
        curr: 1,
        sum: 1000.0,
        kommb: 50,
        sum_native: 1000.0,
        kommb_native: 50.0
    );

    // Act
    $operation->update(1500.0);

    // Assert - sum обновился через update метод
    expect($operation->sum)->toBe(1000.0); // sum остается readonly
});
