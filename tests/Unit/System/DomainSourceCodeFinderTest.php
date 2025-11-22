<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service;

use App\System\DomainSourceCodeFinder;
use ArrayIterator;
use Mockery;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

it('gets domain dirs', function (): void {
    // Arrange
    $projectDir = '/app';

    $finderMock = Mockery::mock(Finder::class);
    $service = new DomainSourceCodeFinder($finderMock, $projectDir);

    $file1 = Mockery::mock(SplFileInfo::class);
    $file1->shouldReceive('getRelativePathname')->andReturn('Finance/Entity');
    $file2 = Mockery::mock(SplFileInfo::class);
    $file2->shouldReceive('getRelativePathname')->andReturn('User/Dto');

    $finderMock->shouldReceive('files')->andReturnSelf();
    $finderMock->shouldReceive('in')->with('/app/src/Domain')->andReturnSelf();
    $finderMock->shouldReceive('directories')->andReturnSelf();
    $finderMock->shouldReceive('getIterator')->andReturn(new ArrayIterator([$file1, $file2]));

    // Act
    $dirs = $service->getDomainDirs();

    // Assert
    expect($dirs)->toBe([['Finance', 'Entity'], ['User', 'Dto']]);
});

it('gets enum reflections', function (): void {
    // Arrange
    $projectDir = '/app';

    $finderMock = Mockery::mock(Finder::class);
    $service = new DomainSourceCodeFinder($finderMock, $projectDir);

    $file1 = Mockery::mock(SplFileInfo::class);
    $file1->shouldReceive('getRelativePathname')->andReturn('Finance/Enum/SomeEnum.php');

    $finderMock->shouldReceive('files')->andReturnSelf();
    $finderMock->shouldReceive('in')->with('/app/src/Domain')->andReturnSelf();
    $finderMock->shouldReceive('name')->with('*.php')->andReturnSelf();
    $finderMock->shouldReceive('getIterator')->andReturn(new ArrayIterator([$file1]));

    // We need a real enum for the test
    if (!class_exists('App\\Domain\\Finance\\Enum\\SomeEnum')) {
        eval("namespace App\\Domain\\Finance\\Enum; enum SomeEnum: string { case A = 'a'; }");
    }

    // Act
    $reflections = $service->getEnumReflections();

    // Assert
    $reflectionArray = iterator_to_array($reflections);
    expect($reflectionArray)->toHaveCount(1);
    expect($reflectionArray[0]->getName())->toBe('App\\Domain\\Finance\\Enum\\SomeEnum');
});

it('gets class reflections', function (): void {
    // Arrange
    $projectDir = '/app';

    $finderMock = Mockery::mock(Finder::class);
    $service = new DomainSourceCodeFinder($finderMock, $projectDir);

    $file1 = Mockery::mock(SplFileInfo::class);
    $file1->shouldReceive('getRelativePathname')->andReturn('User/Entity/User.php');

    $finderMock->shouldReceive('files')->andReturnSelf();
    $finderMock->shouldReceive('in')->with('/app/src/Domain')->andReturnSelf();
    $finderMock->shouldReceive('name')->with('*.php')->andReturnSelf();
    $finderMock->shouldReceive('path')->with('Entity')->andReturnSelf();
    $finderMock->shouldReceive('getIterator')->andReturn(new ArrayIterator([$file1]));

    if (!class_exists('App\\Domain\\User\\Entity\\User')) {
        eval("namespace App\\Domain\\User\\Entity; class User {}");
    }

    // Act
    $reflections = $service->getClassReflections('*.php', 'Entity');

    // Assert
    $reflectionArray = iterator_to_array($reflections);
    expect($reflectionArray)->toHaveCount(1);
    expect($reflectionArray[0]->getName())->toBe('App\\Domain\\User\\Entity\\User');
});
