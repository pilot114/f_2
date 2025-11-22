<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Attribute;

use App\Common\Attribute\CpMenu;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Mockery;
use ReflectionClass;

beforeEach(function (): void {
    $this->currentUser = createSecurityUser(
        id: 456,
    );
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
});

afterEach(function (): void {
    Mockery::close();
});

it('creates CpMenu with expression', function (): void {
    // Arrange & Act
    $attribute = new CpMenu('main.menu');

    // Assert
    expect($attribute->expression)->toBe('main.menu');
});

it('sets context correctly', function (): void {
    // Arrange
    $attribute = new CpMenu('main.menu');

    // Act
    $attribute->setContext($this->currentUser, $this->secRepo);

    // Assert - using reflection to check protected properties
    $reflection = new ReflectionClass($attribute);
    $userProperty = $reflection->getProperty('currentUser');
    $userProperty->setAccessible(true);
    $repoProperty = $reflection->getProperty('secRepo');
    $repoProperty->setAccessible(true);

    expect($userProperty->getValue($attribute))->toBe($this->currentUser)
        ->and($repoProperty->getValue($attribute))->toBe($this->secRepo);
});

it('returns true when menu permission is granted', function (): void {
    // Arrange
    $attribute = new CpMenu('finance.reports');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpMenu')
        ->with(456, 'finance.reports')
        ->once()
        ->andReturn(true);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeTrue();
});

it('returns false when menu permission is not granted', function (): void {
    // Arrange
    $attribute = new CpMenu('admin.settings');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpMenu')
        ->with(456, 'admin.settings')
        ->once()
        ->andReturn(false);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeFalse();
});

it('checks different menu names', function (string $menuName, bool $hasPermission): void {
    // Arrange
    $attribute = new CpMenu($menuName);
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpMenu')
        ->with(456, $menuName)
        ->once()
        ->andReturn($hasPermission);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBe($hasPermission);
})->with([
    ['hr.dashboard', true],
    ['finance.dashboard', false],
    ['admin.panel', true],
]);
