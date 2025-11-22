<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Position;

beforeEach(function (): void {
    $this->entity = new Position(
        name: 'Test',
        description: 'Description',
    );
});

it('create', function (): void {
    expect($this->entity)->toBeInstanceOf(Position::class);
});

it('to array', function (): void {
    expect($this->entity->toArray())
        ->toBeArray()
        ->toBe([
            'name'        => 'Test',
            'description' => 'Description',
        ]);
});

it('name getter', function (): void {
    expect($this->entity->getName())->toBe('Test');
});

it('description getter', function (): void {
    expect($this->entity->getDescription())->toBe('Description');
});
