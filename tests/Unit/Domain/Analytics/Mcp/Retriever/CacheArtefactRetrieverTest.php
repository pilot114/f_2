<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\Retriever;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use Database\Connection\CpConnection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->conn = Mockery::mock(CpConnection::class);
    $this->retriever = new CacheArtefactRetriever($this->conn);
});

it('retrieves name list for specific type', function (): void {
    $generator = (function () {
        yield [
            'name' => 'test.table1',
        ];
        yield [
            'name' => 'test.table2',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(Mockery::on(fn ($sql): bool => str_contains($sql, 'SELECT name')), [
            'type' => 'TABLE',
        ])
        ->andReturn($generator);

    $result = $this->retriever->getNameList(ArtefactType::TABLE);

    expect($result)->toBe(['test.table1', 'test.table2']);
});

it('has get method with correct signature', function (): void {
    $reflection = new ReflectionClass(CacheArtefactRetriever::class);

    expect($reflection->hasMethod('get'))->toBeTrue();

    $method = $reflection->getMethod('get');
    expect($method->isPublic())->toBeTrue();
});

it('searches artefacts with query string', function (): void {
    $generator = (function () {
        yield [
            'name' => 'test.table',
            'type' => 'TABLE',
        ];
        yield [
            'name' => 'test.view',
            'type' => 'VIEW',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'LIKE')),
            [
                'q' => '%test%',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('test');

    expect($result)->toHaveCount(2)
        ->and($result[0]['name'])->toBe('test.table')
        ->and($result[1]['name'])->toBe('test.view');
});

it('searches artefacts with specific type', function (): void {
    $generator = (function () {
        yield [
            'name' => 'test.table',
            'type' => 'TABLE',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'AND type = :type')),
            [
                'q'    => '%test%',
                'type' => 'TABLE',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('test', ArtefactType::TABLE);

    expect($result)->toHaveCount(1)
        ->and($result[0]['type'])->toBe('TABLE');
});

it('returns empty array for getChunk', function (): void {
    $result = $this->retriever->getChunk(['test.table'], ArtefactType::TABLE);

    expect($result)->toBeArray()->toBeEmpty();
});

it('has search method with correct signature', function (): void {
    $reflection = new ReflectionClass(CacheArtefactRetriever::class);

    expect($reflection->hasMethod('search'))->toBeTrue();

    $method = $reflection->getMethod('search');
    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('searches artefacts without type filter', function (): void {
    $generator = (function () {
        yield [
            'name' => 'test.procedure',
            'type' => 'PROCEDURE',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'LIKE') && !str_contains($sql, 'AND type')),
            [
                'q' => '%procedure%',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('procedure', null);

    expect($result)->toHaveCount(1)
        ->and($result[0]['name'])->toBe('test.procedure')
        ->and($result[0]['type'])->toBe('PROCEDURE');
});

it('converts search query to lowercase', function (): void {
    $generator = (function () {
        yield [
            'name' => 'test.table',
            'type' => 'TABLE',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::type('string'),
            Mockery::on(fn ($params): bool => $params['q'] === '%test%')
        )
        ->andReturn($generator);

    $result = $this->retriever->search('TEST');

    expect($result)->toHaveCount(1);
});
