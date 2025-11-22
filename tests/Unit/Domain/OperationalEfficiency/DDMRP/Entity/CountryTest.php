<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Country;

it('creates country with id and name', function (): void {
    $country = new Country(
        id: 1,
        name: 'Russia'
    );

    $response = $country->toCountryResponse();

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Russia');
});

it('converts to CountryResponse', function (): void {
    $country = new Country(
        id: 2,
        name: 'Kazakhstan'
    );

    $response = $country->toCountryResponse();

    expect($response->id)->toBe(2)
        ->and($response->name)->toBe('Kazakhstan');
});
