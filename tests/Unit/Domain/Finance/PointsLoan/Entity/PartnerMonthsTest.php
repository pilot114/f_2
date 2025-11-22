<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Country;
use App\Domain\Finance\PointsLoan\Entity\Partner;

it('calculates months correctly when partner ended cooperation', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-04-29');
    $endDate = new DateTimeImmutable('2024-06-01');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(2); // апрель + май (июнь не считаем)
});

it('calculates months correctly when partner is still active', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-04-29');
    $mockCurrentDate = new DateTimeImmutable('2024-06-01');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: null
    );

    // Act & Assert
    expect($partner->getMonthsInCompany($mockCurrentDate))->toBe(3); // апрель + май + июнь
});

it('calculates months correctly for same month start and end', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-03-15');
    $endDate = new DateTimeImmutable('2024-03-15');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(1); // март (месяц окончания не считаем, но месяц начала всегда считаем)
});

it('calculates months correctly for full year', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-01-01');
    $endDate = new DateTimeImmutable('2024-12-31');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(11); // весь год (декабрь не считаем)
});

it('calculates months correctly across different years', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2023-12-30');
    $endDate = new DateTimeImmutable('2024-01-01');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(1); // декабрь 2023 (январь 2024 не считаем)
});

it('calculates months correctly for long period', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2020-01-15');
    $endDate = new DateTimeImmutable('2023-06-15');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(41); // 3 года 5 месяцев - 1 (месяц окончания не считаем)
});

it('calculates months correctly for active partner with long period', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2020-01-15');
    $mockCurrentDate = new DateTimeImmutable('2023-06-15');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: null
    );

    // Act & Assert
    expect($partner->getMonthsInCompany($mockCurrentDate))->toBe(42); // 3 года 5 месяцев (текущий месяц считается)
});

it('calculates months correctly for edge case - last day of month', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-01-31');
    $endDate = new DateTimeImmutable('2024-02-29');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(1); // январь (февраль не считаем)
});

it('calculates months correctly for edge case - first day of month', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-01-01');
    $endDate = new DateTimeImmutable('2024-02-01');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(1); // январь (февраль не считаем)
});

it('calculates months correctly for leap year', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2024-02-29'); // високосный год
    $endDate = new DateTimeImmutable('2024-03-01');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(1); // февраль (март не считаем)
});

it('calculates months correctly for 10 years period', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2014-01-15');
    $endDate = new DateTimeImmutable('2024-01-15');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: $endDate
    );

    // Act & Assert
    expect($partner->getMonthsInCompany())->toBe(120); // 10 лет = 120 месяцев (январь 2024 не считаем)
});

it('calculates months correctly for 10 years active partner', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $startDate = new DateTimeImmutable('2014-01-15');
    $mockCurrentDate = new DateTimeImmutable('2024-01-15');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: $startDate,
        closedAt: null
    );

    // Act & Assert
    expect($partner->getMonthsInCompany($mockCurrentDate))->toBe(121); // 10 лет + 1 месяц (текущий январь 2024 считается)
});
