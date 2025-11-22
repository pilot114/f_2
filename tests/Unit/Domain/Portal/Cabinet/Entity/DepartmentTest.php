<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Department;

beforeEach(function (): void {
    $this->entity = new Department(
        id: 1,
        name: 'Test',
        parentId: 2
    );

    $this->child = new Department(
        id: 5,
        name: 'Test child',
        parentId: 6
    );
});

it('create', function (): void {
    expect($this->entity)->toBeInstanceOf(Department::class);
});

it('to array', function (): void {
    expect($this->entity->toArray())
        ->toBeArray()
        ->toBe([
            'id'    => 1,
            'name'  => 'Test',
            'child' => null,
        ]);
});

it('id getter', function (): void {
    expect($this->entity->getId())->toBe(1);
});

it('name getter', function (): void {
    expect($this->entity->getName())->toBe('Test');
});

it('parent getter', function (): void {
    expect($this->entity->getParentId())->toBe(2);
});

it('level check', function (): void {
    expect($this->entity->isTopLevel())->toBeFalse();
});

it('get child', function (): void {
    expect($this->entity->getChild())->toBeNull();
});

it('add child', function (): void {
    $this->entity->addChild($this->child);
    expect($this->entity->getChild())->toBeInstanceOf(Department::class);
});
