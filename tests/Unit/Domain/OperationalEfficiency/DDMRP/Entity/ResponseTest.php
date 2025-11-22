<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Response;

it('creates response with id and name', function (): void {
    $response = new Response(
        id: 1,
        name: 'Manager'
    );

    expect($response->id)->toBe(1);
});

it('converts to ResponseResponse', function (): void {
    $response = new Response(
        id: 2,
        name: 'Team Lead'
    );

    $responseDto = $response->toResponseResponse();

    expect($responseDto->id)->toBe(2)
        ->and($responseDto->name)->toBe('Team Lead');
});
