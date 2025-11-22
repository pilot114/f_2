<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\GrandManager;

it('creates grand manager with id and name', function (): void {
    $manager = new GrandManager(
        id: 1,
        name: 'John Manager'
    );

    expect($manager->id)->toBe(1);
});

it('converts to GrandManagerResponse', function (): void {
    $manager = new GrandManager(
        id: 2,
        name: 'Jane Manager'
    );

    $response = $manager->toGrandManagerResponse();

    expect($response->id)->toBe(2)
        ->and($response->name)->toBe('Jane Manager');
});
