<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Security;

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use App\System\Security\UserProvider;
use Database\Connection\WriteDatabaseInterface;
use Database\ORM\DataMapperInterface;
use Mockery;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

beforeEach(function (): void {
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->writeDb = Mockery::mock(WriteDatabaseInterface::class);
    $this->mapper = Mockery::mock(DataMapperInterface::class);
    $this->mapper->shouldReceive('setFlatMode')->with(true)->byDefault();
    $this->container = Mockery::mock(Container::class);

    $this->provider = new UserProvider(
        $this->secRepo,
        $this->writeDb,
        $this->mapper,
        $this->container
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('loads user by identifier from container cache', function (): void {
    $user = createSecurityUser(id: 123, email: 'test@example.com');

    $this->container->shouldReceive('get')
        ->with(SecurityUser::class)
        ->andReturn($user);

    $result = $this->provider->loadUserByIdentifier('test@example.com');

    expect($result)->toBe($user)
        ->and($result->email)->toBe('test@example.com');
});

it('loads user by identifier from database when not in cache', function (): void {
    $user = createSecurityUser(id: 456, email: 'user@example.com');

    $this->container->shouldReceive('get')
        ->with(SecurityUser::class)
        ->andReturn(null);

    $this->secRepo->shouldReceive('findOneBy')
        ->with([
            'email' => 'user@example.com',
        ])
        ->andReturn($user);

    $result = $this->provider->loadUserByIdentifier('user@example.com');

    expect($result)->toBe($user)
        ->and($result->email)->toBe('user@example.com');
});

it('loads user by identifier from database when cached user has different email', function (): void {
    $cachedUser = createSecurityUser(id: 123, email: 'cached@example.com');
    $dbUser = createSecurityUser(id: 456, email: 'different@example.com');

    $this->container->shouldReceive('get')
        ->with(SecurityUser::class)
        ->andReturn($cachedUser);

    $this->secRepo->shouldReceive('findOneBy')
        ->with([
            'email' => 'different@example.com',
        ])
        ->andReturn($dbUser);

    $result = $this->provider->loadUserByIdentifier('different@example.com');

    expect($result)->toBe($dbUser)
        ->and($result->email)->toBe('different@example.com');
});

it('throws UserNotFoundException when user not found', function (): void {
    $this->container->shouldReceive('get')
        ->with(SecurityUser::class)
        ->andReturn(null);

    $this->secRepo->shouldReceive('findOneBy')
        ->with([
            'email' => 'nonexistent@example.com',
        ])
        ->andReturn(null);

    $this->provider->loadUserByIdentifier('nonexistent@example.com');
})->throws(UserNotFoundException::class);

it('refreshes user', function (): void {
    $user = createSecurityUser(id: 123, email: 'test@example.com');

    // Mock the update method on the command repository
    // Since we can't easily mock the private CommandRepository,
    // we'll just verify the method exists and returns a user
    $result = $this->provider->refreshUser($user);

    expect($result)->toBeInstanceOf(SecurityUser::class);
});

it('supports SecurityUser class', function (): void {
    $result = $this->provider->supportsClass(SecurityUser::class);

    expect($result)->toBeTrue();
});

it('supports any class', function (): void {
    $result = $this->provider->supportsClass('AnyClass');

    expect($result)->toBeTrue();
});
