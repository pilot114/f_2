<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Attribute;

use App\Common\Attribute\CpAction;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Mockery;
use ReflectionClass;

beforeEach(function (): void {
    $this->currentUser = createSecurityUser(
        id: 123,
    );
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
});

afterEach(function (): void {
    Mockery::close();
});

it('creates CpAction with expression', function (): void {
    // Arrange & Act
    $attribute = new CpAction('test.action');

    // Assert
    expect($attribute->expression)->toBe('test.action');
});

it('sets context correctly', function (): void {
    // Arrange
    $attribute = new CpAction('test.action');

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

it('checks permission with simple expression', function (): void {
    // Arrange
    $attribute = new CpAction('finance.kpi');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'finance.kpi')
        ->once()
        ->andReturn(true);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeTrue();
});

it('checks permission with complex expression using AND', function (): void {
    // Arrange
    $attribute = new CpAction('finance.kpi and hr.achievements');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'finance.kpi')
        ->once()
        ->andReturn(true);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'and')
        ->once()
        ->andReturn(false); // 'and' is not a permission, so return false

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'hr.achievements')
        ->once()
        ->andReturn(true);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeTrue();
});

it('checks permission with complex expression using OR', function (): void {
    // Arrange
    $attribute = new CpAction('finance.kpi or hr.achievements');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'finance.kpi')
        ->once()
        ->andReturn(false);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'or')
        ->once()
        ->andReturn(false); // 'or' is not a permission, so return false

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'hr.achievements')
        ->once()
        ->andReturn(true);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeTrue();
});

it('returns false when permission is not granted', function (): void {
    // Arrange
    $attribute = new CpAction('restricted.action');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'restricted.action')
        ->once()
        ->andReturn(false);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeFalse();
});

it('handles complex nested expressions', function (): void {
    // Arrange
    $attribute = new CpAction('(finance.kpi and hr.achievements) or admin.access');
    $attribute->setContext($this->currentUser, $this->secRepo);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'finance.kpi')
        ->once()
        ->andReturn(false);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'and')
        ->once()
        ->andReturn(false);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'hr.achievements')
        ->once()
        ->andReturn(true);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'or')
        ->once()
        ->andReturn(false);

    $this->secRepo->shouldReceive('hasCpAction')
        ->with(123, 'admin.access')
        ->once()
        ->andReturn(true);

    // Act
    $result = $attribute->check();

    // Assert
    expect($result)->toBeTrue();
});
