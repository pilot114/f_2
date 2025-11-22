<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\ExcelLoanRepresentation;

it('creates excel loan representation with all properties', function (): void {
    // Arrange & Act
    $representation = new ExcelLoanRepresentation(
        id: 1,
        contract: 'CONTRACT123',
        name: 'John Doe',
        country: 'USA',
        months: 12,
        monthlyPayment: 100.0,
        summ: 1200.0,
        guarantorName: 'Jane Smith'
    );

    // Assert
    expect($representation->id)->toBe(1);
    expect($representation->contract)->toBe('CONTRACT123');
    expect($representation->name)->toBe('John Doe');
    expect($representation->country)->toBe('USA');
    expect($representation->months)->toBe(12);
    expect($representation->monthlyPayment)->toBe(100.0);
    expect($representation->summ)->toBe(1200.0);
    expect($representation->guarantorName)->toBe('Jane Smith');
});

it('creates excel loan representation without guarantor', function (): void {
    // Arrange & Act
    $representation = new ExcelLoanRepresentation(
        id: 2,
        contract: 'CONTRACT456',
        name: 'Bob Wilson',
        country: 'Canada',
        months: 6,
        monthlyPayment: 50.0,
        summ: 300.0
    );

    // Assert
    expect($representation->id)->toBe(2);
    expect($representation->contract)->toBe('CONTRACT456');
    expect($representation->name)->toBe('Bob Wilson');
    expect($representation->country)->toBe('Canada');
    expect($representation->months)->toBe(6);
    expect($representation->monthlyPayment)->toBe(50.0);
    expect($representation->summ)->toBe(300.0);
    expect($representation->guarantorName)->toBeNull();
});

it('returns correct headers', function (): void {
    // Act
    $headers = ExcelLoanRepresentation::getHeaders();

    // Assert
    expect($headers)->toBe([
        'Контракт',
        'ФИО',
        'Страна',
        'Примечание',
        'Баллы',
    ]);
});

it('returns correct columns data with guarantor', function (): void {
    // Arrange
    $representation = new ExcelLoanRepresentation(
        id: 1,
        contract: 'CONTRACT123',
        name: 'John Doe',
        country: 'USA',
        months: 12,
        monthlyPayment: 100.0,
        summ: 1200.0,
        guarantorName: 'Jane Smith',
        guarantorContract: "123"
    );

    // Act
    $columnsData = $representation->getColumnsData();

    // Assert
    expect($columnsData)->toBe([
        'CONTRACT123',
        'John Doe',
        'USA',
        "На 12 месяцев по 100 баллов, гарант ({$representation->guarantorContract}) Jane Smith",
        1200.0,
    ]);
});

it('returns correct columns data without guarantor', function (): void {
    // Arrange
    $representation = new ExcelLoanRepresentation(
        id: 2,
        contract: 'CONTRACT456',
        name: 'Bob Wilson',
        country: 'Canada',
        months: 6,
        monthlyPayment: 50.0,
        summ: 300.0
    );

    // Act
    $columnsData = $representation->getColumnsData();

    // Assert
    expect($columnsData)->toBe([
        'CONTRACT456',
        'Bob Wilson',
        'Canada',
        'На 6 месяцев по 50 баллов',
        300.0,
    ]);
});

it('returns dash when months or monthly payment is zero', function (): void {
    // Arrange
    $representation = new ExcelLoanRepresentation(
        id: 3,
        contract: 'CONTRACT789',
        name: 'Test User',
        country: 'Test Country',
        months: 0,
        monthlyPayment: 0.0,
        summ: 0.0
    );

    // Act
    $columnsData = $representation->getColumnsData();

    // Assert
    expect($columnsData[3])->toBe('-'); // Примечание
});
