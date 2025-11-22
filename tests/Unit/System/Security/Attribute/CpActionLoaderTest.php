<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Security\Attribute;

use App\Common\Attribute\CpAction;
use App\System\DomainSourceCodeFinder;
use App\System\Security\Attribute\CpActionLoader;
use Mockery;
use ReflectionClass;
use Symfony\Contracts\Cache\CacheInterface;

class TestController
{
    #[CpAction('test.permission')]
    public function testMethod(): void
    {
    }

    public function methodWithoutAttribute(): void
    {
    }
}

beforeEach(function (): void {
    $this->fileLoader = Mockery::mock(DomainSourceCodeFinder::class);
    $this->cache = Mockery::mock(CacheInterface::class);
    $this->loader = new CpActionLoader(
        $this->fileLoader,
        $this->cache,
        'test'
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('loads cp actions from controller methods', function (): void {
    // Arrange
    $refController = new ReflectionClass(TestController::class);

    $generator = (function () use ($refController) {
        yield $refController;
    })();

    $this->fileLoader->shouldReceive('getClassReflections')
        ->with('*Controller.php')
        ->andReturn($generator);

    // Act
    $result = iterator_to_array($this->loader->load());

    // Assert
    expect($result)->toHaveCount(1);
    expect($result)->toHaveKey('App\Tests\Unit\System\Security\Attribute\TestController::testMethod');
    expect($result['App\Tests\Unit\System\Security\Attribute\TestController::testMethod'])->toBeInstanceOf(CpAction::class);
});

it('returns null when searching for non-existent fqn', function (): void {
    // Arrange
    $generator = (function () {
        if (false) { // Never executes, but makes it a generator
            yield;
        }
    })();

    $this->fileLoader->shouldReceive('getClassReflections')
        ->with('*Controller.php')
        ->andReturn($generator);

    // Act
    $result = $this->loader->loadByFqn('NonExistent::method');

    // Assert
    expect($result)->toBeNull();
});

it('returns cp action when searching by existing fqn', function (): void {
    // Arrange
    $refController = new ReflectionClass(TestController::class);

    $generator = (function () use ($refController) {
        yield $refController;
    })();

    $this->fileLoader->shouldReceive('getClassReflections')
        ->with('*Controller.php')
        ->andReturn($generator);

    // Act
    $result = $this->loader->loadByFqn('App\Tests\Unit\System\Security\Attribute\TestController::testMethod');

    // Assert
    expect($result)->toBeInstanceOf(CpAction::class);
    expect($result->expression)->toBe('test.permission');
});

it('includes only methods with cp action attributes', function (): void {
    // Arrange
    $refController = new ReflectionClass(TestController::class);

    $generator = (function () use ($refController) {
        yield $refController;
    })();

    $this->fileLoader->shouldReceive('getClassReflections')
        ->with('*Controller.php')
        ->andReturn($generator);

    // Act
    $result = iterator_to_array($this->loader->load());

    // Assert - should only have 1 method (testMethod), not methodWithoutAttribute
    expect($result)->toHaveCount(1);
    expect($result)->toHaveKey('App\Tests\Unit\System\Security\Attribute\TestController::testMethod');
});

it('uses cache in production environment', function (): void {
    // Arrange
    $loader = new CpActionLoader(
        $this->fileLoader,
        $this->cache,
        'prod'
    );

    $cachedData = [
        'TestController::method' => new CpAction('cached.permission'),
    ];
    $this->cache->shouldReceive('get')
        ->with('ControllerAttributeLoader_doLoad_CpAction', Mockery::type('callable'))
        ->andReturn($cachedData);

    // Act
    $result = iterator_to_array($loader->load());

    // Assert
    expect($result)->toBe($cachedData);
});
