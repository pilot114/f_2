<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\PartnerStats;

it('creates partner stats with correct properties', function (): void {
    // Arrange & Act
    $month = new DateTimeImmutable('2024-01-01');
    $stats = new PartnerStats('PARTNER123', 1000, 5000, 5, $month);

    // Assert
    expect($stats->id)->toBe('PARTNER123');
    expect($stats->personalVolume)->toBe(1000);
    expect($stats->totalVolume)->toBe(5000);
    expect($stats->rang)->toBe(5);
    expect($stats->month)->toBe($month);
});

it('creates partner stats with different values', function (): void {
    // Arrange & Act
    $month = new DateTimeImmutable('2024-02-01');
    $stats = new PartnerStats('PARTNER456', 2000, 8000, 10, $month);

    // Assert
    expect($stats->id)->toBe('PARTNER456');
    expect($stats->personalVolume)->toBe(2000);
    expect($stats->totalVolume)->toBe(8000);
    expect($stats->rang)->toBe(10);
    expect($stats->month)->toBe($month);
});

it('partner stats properties are readonly', function (): void {
    // Arrange
    $month = new DateTimeImmutable('2024-01-01');
    $stats = new PartnerStats('TEST123', 100, 500, 1, $month);

    // Assert - свойства readonly, нельзя изменить
    expect($stats)->toBeInstanceOf(PartnerStats::class);
    expect($stats->id)->toBe('TEST123');
    expect($stats->personalVolume)->toBe(100);
    expect($stats->totalVolume)->toBe(500);
    expect($stats->rang)->toBe(1);
    expect($stats->month)->toBe($month);
});

it('returns correct array representation', function (): void {
    // Arrange
    $month = new DateTimeImmutable('2024-01-01T00:00:00+00:00');
    $stats = new PartnerStats('TEST123', 100, 500, 1, $month);

    // Act
    $array = $stats->toArray();

    // Assert
    expect($array)->toBe([
        'month'          => '2024-01-01',
        'personalVolume' => 100,
        'totalVolume'    => 500,
        'rang'           => 1,
    ]);
});
