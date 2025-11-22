<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Portal\Security\Entity;

use App\Domain\Portal\Security\Entity\Permission;
use ReflectionClass;

it('создаётся с необходимыми параметрами', function (): void {
    $permission = new Permission(
        id: '1',
        type: 1,
        name: 'test_permission',
        access_type: 'read',
        resource_id: 123,
        resource_type: 'document'
    );

    expect($permission)->toBeInstanceOf(Permission::class);
});

it('возвращает корректное имя', function (): void {
    $permission = new Permission(
        id: '1',
        type: 1,
        name: 'test_permission',
        access_type: 'read',
        resource_id: 123,
        resource_type: 'document'
    );

    expect($permission->getName())->toBe('test_permission');
});

it('имеет метод __toString', function (): void {
    $permission = new Permission(
        id: '1',
        type: 1,
        name: 'test_permission',
        access_type: 'read',
        resource_id: 123,
        resource_type: 'document'
    );

    $string = (string) $permission;

    expect($string)->toBeString()
        ->toContain('test_permission')
        ->toContain('read')
        ->toContain('document');
});

it('имеет атрибут Entity', function (): void {
    $reflection = new ReflectionClass(Permission::class);
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

it('имеет все необходимые свойства', function (): void {
    $reflection = new ReflectionClass(Permission::class);

    expect($reflection->hasProperty('id'))->toBeTrue()
        ->and($reflection->hasProperty('type'))->toBeTrue()
        ->and($reflection->hasProperty('name'))->toBeTrue()
        ->and($reflection->hasProperty('access_type'))->toBeTrue()
        ->and($reflection->hasProperty('resource_id'))->toBeTrue()
        ->and($reflection->hasProperty('resource_type'))->toBeTrue();
});
