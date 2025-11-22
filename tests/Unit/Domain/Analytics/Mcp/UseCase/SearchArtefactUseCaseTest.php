<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\UseCase;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use App\Domain\Analytics\Mcp\UseCase\SearchArtefactUseCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(SearchArtefactUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('retriever');
});

it('has search method with correct signature', function (): void {
    $reflection = new ReflectionClass(SearchArtefactUseCase::class);
    $method = $reflection->getMethod('search');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('query')
        ->and($parameters[1]->getName())->toBe('type');
});

it('search method returns array with correct structure', function (): void {
    $reflection = new ReflectionClass(SearchArtefactUseCase::class);
    $method = $reflection->getMethod('search');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('array');
});

it('search method has nullable type parameter', function (): void {
    $reflection = new ReflectionClass(SearchArtefactUseCase::class);
    $method = $reflection->getMethod('search');

    $parameters = $method->getParameters();
    $typeParam = $parameters[1];

    expect($typeParam->allowsNull())->toBeTrue()
        ->and($typeParam->isDefaultValueAvailable())->toBeTrue()
        ->and($typeParam->getDefaultValue())->toBeNull();
});

it('search returns items and total', function (): void {
    $retriever = Mockery::mock(CacheArtefactRetriever::class);

    $retriever->shouldReceive('search')
        ->with('test', null)
        ->once()
        ->andReturn([
            [
                'name' => 'test.table1',
                'type' => 'TABLE',
            ],
            [
                'name' => 'test.table2',
                'type' => 'TABLE',
            ],
        ]);

    $useCase = new SearchArtefactUseCase($retriever);
    $result = $useCase->search('test');

    expect($result)->toHaveKey('items')
        ->and($result)->toHaveKey('total')
        ->and($result['items'])->toHaveCount(2)
        ->and($result['total'])->toBe(2);
});

it('search with specific type', function (): void {
    $retriever = Mockery::mock(CacheArtefactRetriever::class);

    $retriever->shouldReceive('search')
        ->with('user', ArtefactType::TABLE)
        ->once()
        ->andReturn([
            [
                'name' => 'test.users',
                'type' => 'TABLE',
            ],
        ]);

    $useCase = new SearchArtefactUseCase($retriever);
    $result = $useCase->search('user', ArtefactType::TABLE);

    expect($result)->toHaveKey('items')
        ->and($result)->toHaveKey('total')
        ->and($result['items'])->toHaveCount(1)
        ->and($result['total'])->toBe(1);
});

it('search returns empty result', function (): void {
    $retriever = Mockery::mock(CacheArtefactRetriever::class);

    $retriever->shouldReceive('search')
        ->with('nonexistent', null)
        ->once()
        ->andReturn([]);

    $useCase = new SearchArtefactUseCase($retriever);
    $result = $useCase->search('nonexistent');

    expect($result)->toHaveKey('items')
        ->and($result)->toHaveKey('total')
        ->and($result['items'])->toBeEmpty()
        ->and($result['total'])->toBe(0);
});

it('can be instantiated', function (): void {
    $retriever = Mockery::mock(CacheArtefactRetriever::class);

    $useCase = new SearchArtefactUseCase($retriever);

    expect($useCase)->toBeInstanceOf(SearchArtefactUseCase::class);
});
