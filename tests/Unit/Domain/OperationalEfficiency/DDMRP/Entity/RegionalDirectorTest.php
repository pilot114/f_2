<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\RegionalDirector;

it('creates regional director with id and name', function (): void {
    $director = new RegionalDirector(
        id: 1,
        name: 'John Director'
    );

    expect($director->id)->toBe(1);
});

it('converts to RegionDirectorResponse', function (): void {
    $director = new RegionalDirector(
        id: 2,
        name: 'Jane Director'
    );

    $response = $director->toRegionDirectorResponse();

    expect($response->id)->toBe(2)
        ->and($response->name)->toBe('Jane Director');
});
