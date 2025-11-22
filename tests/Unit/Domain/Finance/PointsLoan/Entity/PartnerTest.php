<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Country;
use App\Domain\Finance\PointsLoan\Entity\Partner;
use App\Domain\Finance\PointsLoan\Entity\PartnerStats;
use App\Domain\Finance\PointsLoan\Entity\Violation;
use App\Domain\Finance\PointsLoan\Enum\ViolationType;

it('creates partner with all properties', function (): void {
    // Arrange & Act
    $country = new Country(1, 'USA');
    $stats = [
        new PartnerStats('STAT1', 1000, 5000, 5, new DateTimeImmutable('2024-01-01')),
        new PartnerStats('STAT2', 2000, 8000, 10, new DateTimeImmutable('2024-02-01')),
    ];
    $violation = new Violation(1, ViolationType::UNDER_CONTROL, 'Test violation');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'John Doe',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),
        stats: $stats,
        violation: $violation
    );

    // Assert
    expect($partner->id)->toBe(1);
    expect($partner->contract)->toBe('CONTRACT123');
    expect($partner->name)->toBe('John Doe');
    expect($partner->getEmailsAsString())->toBe('');
    expect($partner->country)->toBe($country);
    expect($partner->violation)->toBe($violation);
    expect($partner->closedAt)->toBeNull();
});

it('creates partner without optional properties', function (): void {
    // Arrange & Act
    $country = new Country(2, 'Canada');

    $partner = new Partner(
        id: 2,
        contract: 'CONTRACT456',
        name: 'Bob Wilson',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),
    );

    // Assert
    expect($partner->id)->toBe(2);
    expect($partner->contract)->toBe('CONTRACT456');
    expect($partner->name)->toBe('Bob Wilson');
    expect($partner->getEmailsAsString())->toBe('');
    expect($partner->country)->toBe($country);
    expect($partner->violation)->toBeNull();
    expect($partner->closedAt)->toBeNull();
});

it('creates partner with closed date', function (): void {
    // Arrange & Act
    $country = new Country(3, 'UK');
    $closedAt = new DateTimeImmutable('2024-01-01');

    $partner = new Partner(
        id: 3,
        contract: 'CONTRACT789',
        name: 'Closed Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),
        closedAt: $closedAt
    );

    // Assert
    expect($partner->id)->toBe(3);
    expect($partner->closedAt)->toBe($closedAt);
});

it('returns correct active status', function (): void {
    // Arrange
    $country = new Country(1, 'USA');

    $activePartner = new Partner(
        id: 1,
        contract: 'ACTIVE123',
        name: 'Active Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),

    );

    $closedPartner = new Partner(
        id: 2,
        contract: 'CLOSED123',
        name: 'Closed Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),
        closedAt: new DateTimeImmutable('2025-01-01')
    );

    // Assert
    expect($activePartner->isActive())->toBeTrue();
    expect($closedPartner->isActive())->toBeFalse();
});

it('returns correct array representation', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $stats = [
        new PartnerStats('STAT1', 1000, 5000, 5, new DateTimeImmutable('2024-01-01T00:00:00+00:00')),
    ];
    $violation = new Violation(1, ViolationType::UNDER_CONTROL, 'Test violation');

    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'John Doe',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01'),
        stats: $stats,
        violation: $violation
    );

    // Act
    $array = $partner->toArray();

    // Assert
    expect($array['id'])->toBe(1);
    expect($array['contract'])->toBe('CONTRACT123');
    expect($array['name'])->toBe('John Doe');
    expect($array['email'])->toBe('');
    expect($array['isActive'])->toBeTrue();
    expect($array['country'])->toBe($country->toArray());
    expect($array['violation'])->toBe($violation->toArray());
    expect($array['stats'])->toHaveCount(1);
    expect($array['stats'][0]['N'])->toBe(1);
    expect($array['stats'][0]['month'])->toBe('2024-01-01');
});

it('returns empty string when no emails', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );

    // Act & Assert
    expect($partner->getEmailsAsString())->toBe('');
});

it('returns emails as comma-separated string', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );
    $partner->emails = ['test1@example.com', 'test2@example.com'];

    // Act & Assert
    expect($partner->getEmailsAsString())->toBe('test1@example.com, test2@example.com');
});

it('adds valid emails to partner', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );

    // Act
    $partner->addEmails(['test1@example.com', 'test2@example.com']);

    // Assert
    expect($partner->emails)->toBe(['test1@example.com', 'test2@example.com']);
    expect($partner->getEmailsAsString())->toBe('test1@example.com, test2@example.com');
});

it('filters out invalid emails when adding', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );

    // Act
    $partner->addEmails([
        'valid@example.com',
        'invalid-email',
        null,
        '',
        'another@valid.com',
    ]);

    // Assert
    expect($partner->emails)->toBe(['valid@example.com', 'another@valid.com']);
    expect($partner->getEmailsAsString())->toBe('valid@example.com, another@valid.com');
});

it('does not add duplicate emails', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );
    $partner->emails = ['existing@example.com'];

    // Act
    $partner->addEmails(['existing@example.com', 'new@example.com']);

    // Assert
    expect(array_values($partner->emails))->toBe(['existing@example.com', 'new@example.com']);
    expect($partner->getEmailsAsString())->toBe('existing@example.com, new@example.com');
});

it('adds emails to existing ones', function (): void {
    // Arrange
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 1,
        contract: 'CONTRACT123',
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );
    $partner->emails = ['first@example.com'];

    // Act
    $partner->addEmails(['second@example.com', 'third@example.com']);

    // Assert
    expect($partner->emails)->toBe(['first@example.com', 'second@example.com', 'third@example.com']);
    expect($partner->getEmailsAsString())->toBe('first@example.com, second@example.com, third@example.com');
});
