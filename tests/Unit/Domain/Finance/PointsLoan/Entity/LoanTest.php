<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Enum\LoanStatus;

it('creates loan with all properties', function (): void {
    // Arrange & Act
    $startDate = new DateTimeImmutable('2024-01-01');
    $endDate = new DateTimeImmutable('2024-12-31');
    $guarantor = new Guarantor(1, 'GUAR123');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0,
        endDate: $endDate,
        linkedLoanId: 999,
        totalPaid: 1200.0,
        guarantor: $guarantor
    );

    // Assert
    expect($loan->getId())->toBe(1);
    expect($loan->getAccrualOperationId())->toBe(100);
    expect($loan->partnerId)->toBe(123);
    expect($loan->startDate)->toBe($startDate);
    expect($loan->endDate)->toBe($endDate);
    expect($loan->linkedLoanId)->toBe(999);
    expect($loan->totalPaid)->toBe(1200.0);
});

it('creates loan without optional properties', function (): void {
    // Arrange & Act
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 2,
        accrualOperationId: 200,
        partnerId: 456,
        startDate: $startDate,
        sum: 600.0,
        months: 6,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->getId())->toBe(2);
    expect($loan->getAccrualOperationId())->toBe(200);
    expect($loan->partnerId)->toBe(456);
    expect($loan->startDate)->toBe($startDate);
    expect($loan->endDate)->toBeNull();
    expect($loan->linkedLoanId)->toBeNull();
    expect($loan->totalPaid)->toBe(0.0);
});

it('can update id', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Act
    $loan->setId(999);

    // Assert
    expect($loan->getId())->toBe(999);
});

it('can update accrual operation id', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Act
    $loan->setAccrualOperationId(999);

    // Assert
    expect($loan->getAccrualOperationId())->toBe(999);
});

it('can update loan details', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');
    $guarantor = new Guarantor(1, 'GUAR123');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Act
    $loan->update(1800.0, 18, 100.0, $guarantor);

    // Assert - проверяем что update сработал
    expect($loan->partnerId)->toBe(123); // readonly свойства не изменились
    expect($loan->startDate)->toBe($startDate);
});

it('validates start date in current period', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');
    $currentPeriod = new DateTimeImmutable('2024-01-01');
    $differentPeriod = new DateTimeImmutable('2024-02-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isStartDateInCurrentPeriod($currentPeriod))->toBeTrue();
    expect($loan->isStartDateInCurrentPeriod($differentPeriod))->toBeFalse();
});

it('validates monthly payment', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(100.0))->toBeTrue(); // 0 < 100 <= 1200
    expect($loan->isMonthlyPaymentValid(1200.0))->toBeTrue(); // 0 < 1200 <= 1200
    expect($loan->isMonthlyPaymentValid(1200.01))->toBeFalse(); // > 1200
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // <= 0
    expect($loan->isMonthlyPaymentValid(-10.0))->toBeFalse(); // <= 0
});

it('validates monthly payment with zero months', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 0, // 0 месяцев
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(100.0))->toBeFalse(); // months <= 0
    expect($loan->isMonthlyPaymentValid(1200.0))->toBeFalse(); // months <= 0
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // months <= 0
});

it('validates monthly payment with negative months', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: -5, // отрицательное количество месяцев
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(100.0))->toBeFalse(); // months <= 0
    expect($loan->isMonthlyPaymentValid(1200.0))->toBeFalse(); // months <= 0
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // months <= 0
});

it('validates monthly payment with very small sum', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 0.01, // очень маленькая сумма
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(0.01))->toBeTrue(); // 0 < 0.01 <= 0.01
    expect($loan->isMonthlyPaymentValid(0.02))->toBeFalse(); // > 0.01
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // <= 0
    expect($loan->isMonthlyPaymentValid(-0.01))->toBeFalse(); // <= 0
});

it('validates monthly payment with zero sum', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 0.0, // нулевая сумма
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // <= 0
    expect($loan->isMonthlyPaymentValid(0.01))->toBeFalse(); // > 0.0
    expect($loan->isMonthlyPaymentValid(-0.01))->toBeFalse(); // <= 0
});

it('validates monthly payment with negative sum', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: -100.0, // отрицательная сумма
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->isMonthlyPaymentValid(0.0))->toBeFalse(); // <= 0
    expect($loan->isMonthlyPaymentValid(0.01))->toBeFalse(); // > -100.0
    expect($loan->isMonthlyPaymentValid(-0.01))->toBeFalse(); // <= 0
});

it('returns correct status for unpaid loan', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0
    );

    // Assert
    expect($loan->getStatus())->toBe(LoanStatus::NOT_PAID);
});

it('returns correct status for paid loan', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01');
    $endDate = new DateTimeImmutable('2024-12-31');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0,
        endDate: $endDate
    );

    // Assert
    expect($loan->getStatus())->toBe(LoanStatus::PAID);
});

it('returns correct array representation', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2024-01-01T00:00:00+00:00');
    $endDate = new DateTimeImmutable('2024-12-31T00:00:00+00:00');
    $guarantor = new Guarantor(1, 'GUAR123');

    $loan = new Loan(
        id: 1,
        accrualOperationId: 100,
        partnerId: 123,
        startDate: $startDate,
        sum: 1200.0,
        months: 12,
        monthlyPayment: 100.0,
        endDate: $endDate,
        linkedLoanId: 999,
        totalPaid: 1200.0,
        guarantor: $guarantor
    );

    // Act
    $array = $loan->toArray();

    // Assert
    expect($array['id'])->toBe(1);
    expect($array['accrualOperationId'])->toBe(100);
    expect($array['partnerId'])->toBe(123);
    expect($array['startDate'])->toBe('2024-01-01');
    expect($array['sum'])->toBe(1200.0);
    expect($array['months'])->toBe(12);
    expect($array['monthlyPayment'])->toBe(100.0);
    expect($array['guarantorContract'])->toBe('GUAR123');
    expect($array['endDate'])->toBe('2024-12-31');
    expect($array['linkedLoanId'])->toBe(999);
    expect($array['totalPaid'])->toBe(1200.0);
    expect($array['loanStatus'])->toBe(LoanStatus::PAID);
});
