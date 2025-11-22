<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Address;

beforeEach(function (): void {
    $this->entity = new Address(
        city: 'Testoburg',
    );
});

it('create', function (): void {
    expect($this->entity)->toBeInstanceOf(Address::class);
});

it('to array', function (): void {
    expect($this->entity->toArray())
        ->toBeArray()
        ->toBe([
            'city' => 'Testoburg',
        ]);
});

it('city getter', function (): void {
    expect($this->entity->getCityName())->toBe('Testoburg');
});

it('city setter', function (): void {
    $this->entity->setCity('2222');
    expect($this->entity->getCityName())->toBe('2222');
});
