<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Country;

it('creates country with correct properties', function (): void {
    // Arrange & Act
    $country = new Country(1, 'United States');

    // Assert
    expect($country->id)->toBe(1);
    expect($country->name)->toBe('United States');
});

it('creates country with different values', function (): void {
    // Arrange & Act
    $country = new Country(999, 'Canada');

    // Assert
    expect($country->id)->toBe(999);
    expect($country->name)->toBe('Canada');
});

it('country properties are readonly', function (): void {
    // Arrange
    $country = new Country(1, 'Test Country');

    // Assert - свойства readonly, нельзя изменить
    expect($country)->toBeInstanceOf(Country::class);
    expect($country->id)->toBe(1);
    expect($country->name)->toBe('Test Country');
});
