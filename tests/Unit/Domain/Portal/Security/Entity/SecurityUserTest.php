<?php

declare(strict_types=1);

use App\Domain\Portal\Security\Entity\Permission;
use App\Domain\Portal\Security\Entity\Role;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Symfony\Component\Security\Core\User\UserInterface;

it('creates user with basic info', function (): void {
    $user = new SecurityUser(
        id: 42,
        name: 'John Doe',
        email: 'john@example.com',
        login: 'test_login'
    );

    expect($user->id)->toBe(42)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');
});

it('gets email', function (): void {
    $user = new SecurityUser(id: 1, name: 'Test', email: 'test@example.com', login: 'test_login');

    expect($user->email)->toBe('test@example.com');
});

it('gets user identifier as string', function (): void {
    $user = new SecurityUser(id: 123, name: 'Test', email: 'test@example.com', login: 'test_login');

    expect($user->getUserIdentifier())->toBe('123')
        ->and($user->getUserIdentifier())->toBeString();
});

it('returns default ROLE_USER when no roles', function (): void {
    $user = new SecurityUser(id: 1, name: 'Test', email: 'test@example.com', login: 'test_login');

    $roles = $user->getRoles();

    expect($roles)->toBeArray()
        ->and($roles)->toBe(['ROLE_USER']);
});

it('returns user roles with ROLE_USER', function (): void {
    $role1 = new Role(id: 1, name: 'ROLE_ADMIN');
    $role2 = new Role(id: 2, name: 'ROLE_MANAGER');

    $user = new SecurityUser(
        id: 1,
        name: 'Test',
        email: 'test@example.com',
        login: 'test_login',
        roles: [$role1, $role2]
    );

    $roles = $user->getRoles();

    expect($roles)->toContain('ROLE_ADMIN')
        ->and($roles)->toContain('ROLE_MANAGER')
        ->and($roles)->toContain('ROLE_USER')
        ->and(count($roles))->toBe(3);
});

it('returns unique roles', function (): void {
    $role1 = new Role(id: 1, name: 'ROLE_ADMIN');
    $role2 = new Role(id: 2, name: 'ROLE_ADMIN');

    $user = new SecurityUser(
        id: 1,
        name: 'Test',
        email: 'test@example.com',
        login: 'test_login',
        roles: [$role1, $role2]
    );

    $roles = $user->getRoles();

    expect(count($roles))->toBe(2)
        ->and($roles)->toContain('ROLE_ADMIN')
        ->and($roles)->toContain('ROLE_USER');
});

it('returns permissions', function (): void {
    $permission1 = Mockery::mock(Permission::class);
    $permission2 = Mockery::mock(Permission::class);

    $user = new SecurityUser(
        id: 1,
        name: 'Test',
        email: 'test@example.com',
        login: 'test_login',
        permissions: [$permission1, $permission2]
    );

    $permissions = $user->getPermissions();

    expect($permissions)->toBeArray()
        ->and(count($permissions))->toBe(2)
        ->and($permissions[0])->toBe($permission1)
        ->and($permissions[1])->toBe($permission2);

    Mockery::close();
});

it('returns empty permissions array by default', function (): void {
    $user = new SecurityUser(id: 1, name: 'Test', email: 'test@example.com', login: 'test_login');

    expect($user->getPermissions())->toBeArray()
        ->and($user->getPermissions())->toBeEmpty();
});

it('erases credentials without error', function (): void {
    $user = new SecurityUser(id: 1, name: 'Test', email: 'test@example.com', login: 'test_login');

    $user->eraseCredentials();

    expect($user->id)->toBe(1);
});

it('converts to string', function (): void {
    $user = new SecurityUser(id: 42, name: 'John Doe', email: 'john@example.com', login: 'test_login');

    $string = (string) $user;

    expect($string)->toBe('42: John Doe');
});

it('implements UserInterface', function (): void {
    $user = new SecurityUser(id: 1, name: 'Test', email: 'test@example.com', login: 'test_login');

    expect($user)->toBeInstanceOf(UserInterface::class);
});
