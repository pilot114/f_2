<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Portal\Security\Entity;

use App\Domain\Portal\Security\Entity\Role;
use ReflectionClass;

it('создаётся с необходимыми параметрами', function (): void {
    $role = new Role(
        id: 1,
        name: 'admin'
    );

    expect($role)->toBeInstanceOf(Role::class);
});

it('возвращает корректное имя', function (): void {
    $role = new Role(
        id: 1,
        name: 'admin'
    );

    expect($role->getName())->toBe('admin');
});

it('является readonly классом', function (): void {
    $reflection = new ReflectionClass(Role::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('имеет публичное свойство id', function (): void {
    $role = new Role(
        id: 1,
        name: 'admin'
    );

    expect($role->id)->toBe(1);
});

it('имеет атрибут Entity', function (): void {
    $reflection = new ReflectionClass(Role::class);
    $attributes = $reflection->getAttributes();

    $entityAttr = null;
    foreach ($attributes as $attribute) {
        if ($attribute->getName() === 'Database\ORM\Attribute\Entity') {
            $entityAttr = $attribute;
            break;
        }
    }

    expect($entityAttr)->not->toBeNull();
});

it('имеет все необходимые свойства с атрибутами Column', function (): void {
    $reflection = new ReflectionClass(Role::class);

    expect($reflection->hasProperty('id'))->toBeTrue()
        ->and($reflection->hasProperty('name'))->toBeTrue();

    $idProperty = $reflection->getProperty('id');
    $nameProperty = $reflection->getProperty('name');

    expect($idProperty->getAttributes())->not->toBeEmpty()
        ->and($nameProperty->getAttributes())->not->toBeEmpty();
});
