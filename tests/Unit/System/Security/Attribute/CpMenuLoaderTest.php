<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Security\Attribute;

use App\Common\Attribute\CpMenu;
use App\System\DomainSourceCodeFinder;
use App\System\Security\Attribute\CpMenuLoader;
use Mockery;
use ReflectionClass;
use Symfony\Contracts\Cache\CacheInterface;

beforeEach(function (): void {
    $this->fileLoader = Mockery::mock(DomainSourceCodeFinder::class);
    $this->cache = Mockery::mock(CacheInterface::class);
    $this->loader = new CpMenuLoader($this->fileLoader, $this->cache, 'test');
});

afterEach(function (): void {
    Mockery::close();
});

it('should return CpMenu class name', function (): void {
    $reflection = new ReflectionClass($this->loader);
    $method = $reflection->getMethod('getAttributeClass');
    $method->setAccessible(true);
    $result = $method->invoke($this->loader);

    expect($result)->toBe(CpMenu::class);
});
