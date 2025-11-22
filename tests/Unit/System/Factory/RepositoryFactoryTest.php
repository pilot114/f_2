<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Factory;

use App\System\Factory\RepositoryFactory;
use Database\Connection\CpConnection;
use Database\ORM\CommandRepository;
use Database\ORM\DataMapperInterface;
use Database\ORM\QueryRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use stdClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->conn = Mockery::mock(CpConnection::class);
    $this->mapper = Mockery::mock(DataMapperInterface::class);
    $this->mapper->allows('setFlatMode')->andReturnSelf();
    $this->factory = new RepositoryFactory($this->conn, $this->mapper);
});

afterEach(function (): void {
    Mockery::close();
});

it('создаёт CommandRepository для указанной сущности', function (): void {
    $entityName = stdClass::class;

    $repository = $this->factory->command($entityName);

    expect($repository)->toBeInstanceOf(CommandRepository::class);
});

it('создаёт QueryRepository для указанной сущности', function (): void {
    $entityName = stdClass::class;

    $repository = $this->factory->query($entityName);

    expect($repository)->toBeInstanceOf(QueryRepository::class);
});

it('является readonly классом', function (): void {
    $reflection = new ReflectionClass(RepositoryFactory::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('имеет дженерик тип', function (): void {
    $reflection = new ReflectionClass(RepositoryFactory::class);
    $docComment = $reflection->getDocComment();

    expect($docComment)->toBeString()
        ->toContain('@template T of object');
});

it('CommandRepository имеет правильный тип', function (): void {
    $reflection = new ReflectionClass(RepositoryFactory::class);
    $method = $reflection->getMethod('command');
    $docComment = $method->getDocComment();

    expect($docComment)->toBeString()
        ->toContain('@return CommandRepository<T>');
});

it('QueryRepository имеет правильный тип', function (): void {
    $reflection = new ReflectionClass(RepositoryFactory::class);
    $method = $reflection->getMethod('query');
    $docComment = $method->getDocComment();

    expect($docComment)->toBeString()
        ->toContain('@return QueryRepository<T>');
});

it('передаёт зависимости в CommandRepository', function (): void {
    $entityName = stdClass::class;

    $repository = $this->factory->command($entityName);

    // Проверяем, что репозиторий создан и это правильный класс
    expect($repository)->toBeInstanceOf(CommandRepository::class);

    // Проверяем через рефлексию, что зависимости переданы
    $reflection = new ReflectionClass($repository);
    $connProperty = $reflection->getProperty('conn');
    $connProperty->setAccessible(true);

    expect($connProperty->getValue($repository))->toBe($this->conn);
});

it('передаёт зависимости в QueryRepository', function (): void {
    $entityName = stdClass::class;

    $repository = $this->factory->query($entityName);

    // Проверяем, что репозиторий создан и это правильный класс
    expect($repository)->toBeInstanceOf(QueryRepository::class);

    // Проверяем через рефлексию, что зависимости переданы
    $reflection = new ReflectionClass($repository);
    $connProperty = $reflection->getProperty('conn');
    $connProperty->setAccessible(true);

    expect($connProperty->getValue($repository))->toBe($this->conn);
});
