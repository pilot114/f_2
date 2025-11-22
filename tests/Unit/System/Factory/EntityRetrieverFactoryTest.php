<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Factory;

use App\System\Factory\EntityRetrieverFactory;
use Database\Connection\CpConnection;
use Database\Schema\EntityRetriever;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->factory = new EntityRetrieverFactory();
});

it('creates EntityRetriever with ReadOnlySchemaManager', function (): void {
    $connection = Mockery::mock(CpConnection::class);

    $retriever = $this->factory->get($connection);

    expect($retriever)->toBeInstanceOf(EntityRetriever::class);
});

it('passes connection to ReadOnlySchemaManager', function (): void {
    $connection = Mockery::mock(CpConnection::class);

    $retriever = $this->factory->get($connection);

    expect($retriever)->toBeInstanceOf(EntityRetriever::class);
});

it('returns new instance on each call', function (): void {
    $connection = Mockery::mock(CpConnection::class);

    $retriever1 = $this->factory->get($connection);
    $retriever2 = $this->factory->get($connection);

    expect($retriever1)->toBeInstanceOf(EntityRetriever::class)
        ->and($retriever2)->toBeInstanceOf(EntityRetriever::class)
        ->and($retriever1)->not->toBe($retriever2);
});

it('works with different connections', function (): void {
    $connection1 = Mockery::mock(CpConnection::class);
    $connection2 = Mockery::mock(CpConnection::class);

    $retriever1 = $this->factory->get($connection1);
    $retriever2 = $this->factory->get($connection2);

    expect($retriever1)->toBeInstanceOf(EntityRetriever::class)
        ->and($retriever2)->toBeInstanceOf(EntityRetriever::class)
        ->and($retriever1)->not->toBe($retriever2);
});
