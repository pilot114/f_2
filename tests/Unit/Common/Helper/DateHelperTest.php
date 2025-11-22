<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Helper;

use App\Common\Helper\DateHelper;
use DateTimeImmutable;

it('creates DateHelper with date string', function (): void {
    // Arrange & Act
    $helper = new DateHelper('2025-01-15');

    // Assert
    expect($helper->getDate())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($helper->getDate()->format('Y-m-d'))->toBe('2025-01-15');
});

it('has Russian month names constant', function (): void {
    // Assert
    expect(DateHelper::MONTH_NAMES)->toBeArray()
        ->and(DateHelper::MONTH_NAMES)->toHaveCount(12)
        ->and(DateHelper::MONTH_NAMES[0])->toBe('Январь')
        ->and(DateHelper::MONTH_NAMES[11])->toBe('Декабрь');
});

it('returns Russian month and year for January', function (): void {
    // Arrange
    $helper = new DateHelper('2025-01-15');

    // Act
    $result = $helper->getRussianMonthAndYear();

    // Assert
    expect($result)->toBe('Январь 2025');
});

it('returns Russian month and year for December', function (): void {
    // Arrange
    $helper = new DateHelper('2024-12-31');

    // Act
    $result = $helper->getRussianMonthAndYear();

    // Assert
    expect($result)->toBe('Декабрь 2024');
});

it('returns Russian month and year for different months', function (string $date, string $expected): void {
    // Arrange
    $helper = new DateHelper($date);

    // Act
    $result = $helper->getRussianMonthAndYear();

    // Assert
    expect($result)->toBe($expected);
})->with([
    ['2025-02-10', 'Февраль 2025'],
    ['2025-03-20', 'Март 2025'],
    ['2025-04-05', 'Апрель 2025'],
    ['2025-05-15', 'Май 2025'],
    ['2025-06-25', 'Июнь 2025'],
    ['2025-07-30', 'Июль 2025'],
    ['2025-08-12', 'Август 2025'],
    ['2025-09-18', 'Сентябрь 2025'],
    ['2025-10-22', 'Октябрь 2025'],
    ['2025-11-08', 'Ноябрь 2025'],
]);

it('formats date in Russian format with default pattern', function (): void {
    // Arrange
    $date = new DateTimeImmutable('2024-06-05');

    // Act
    $result = DateHelper::ruDateFormat($date);

    // Assert
    expect($result)->toContain('2024')
        ->and($result)->toContain('июня');
});

it('formats date with custom pattern', function (): void {
    // Arrange
    $date = new DateTimeImmutable('2025-01-15');

    // Act
    $result = DateHelper::ruDateFormat($date, 'MMMM');

    // Assert
    expect($result)->toBe('января');
});

it('formats date with year only pattern', function (): void {
    // Arrange
    $date = new DateTimeImmutable('2025-06-15');

    // Act
    $result = DateHelper::ruDateFormat($date, 'yyyy');

    // Assert
    expect($result)->toBe('2025');
});

it('formats date with full pattern', function (): void {
    // Arrange
    $date = new DateTimeImmutable('2025-06-15');

    // Act
    $result = DateHelper::ruDateFormat($date, 'd MMMM yyyy');

    // Assert
    expect($result)->toContain('15')
        ->and($result)->toContain('июня')
        ->and($result)->toContain('2025');
});

it('preserves immutability of date', function (): void {
    // Arrange
    $helper = new DateHelper('2025-01-15');
    $originalDate = $helper->getDate();

    // Act
    $retrievedDate1 = $helper->getDate();
    $retrievedDate2 = $helper->getDate();

    // Assert
    expect($retrievedDate1)->toBe($originalDate)
        ->and($retrievedDate2)->toBe($originalDate);
});

it('handles different date string formats', function (string $dateString): void {
    // Arrange & Act
    $helper = new DateHelper($dateString);

    // Assert
    expect($helper->getDate())->toBeInstanceOf(DateTimeImmutable::class);
})->with([
    '2025-01-15',
    '2025-01-15 10:30:00',
    'now',
    'yesterday',
    '+1 day',
]);

it('returns consistent date object', function (): void {
    // Arrange
    $helper = new DateHelper('2025-01-15 10:30:00');

    // Act
    $date1 = $helper->getDate();
    $date2 = $helper->getDate();

    // Assert
    expect($date1->format('Y-m-d H:i:s'))->toBe($date2->format('Y-m-d H:i:s'));
});
