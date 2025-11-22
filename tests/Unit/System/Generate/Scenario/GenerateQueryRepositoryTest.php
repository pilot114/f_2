<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateQueryRepository;
use Database\Schema\DbObject\Column;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;

require_once __DIR__ . '/SnapshotHelper.php';

it('generates query repository with entities correctly', function (): void {
    $tableName = 'TEST_TABLE';
    $table = new Table($tableName, []);
    $table->columns = [
        new Column('ID', 'integer', isNull: false, comment: 'id'),
        new Column('NAME', 'string', isNull: true, comment: 'name'),
    ];

    $retriever = Mockery::mock(EntityRetriever::class);
    $retriever->shouldReceive('getDbObject')->andReturn($table);

    $generator = new GenerateQueryRepository(
        $retriever,
        'App\Domain\Test\Entity',
        [$tableName]
    );
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\Repository'));

    expect($files)->toHaveKey('TestTableQueryRepository.php');

    assertSnapshot('TestTableQueryRepository.php.snapshot', $files['TestTableQueryRepository.php']);
});

it('generates empty query repository correctly', function (): void {
    $retriever = Mockery::mock(EntityRetriever::class);

    $generator = new GenerateQueryRepository(
        $retriever,
        'App\Domain\Test\Entity',
        null,
        'TestQueryRepository'
    );
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\Repository'));

    expect($files)->toHaveKey('TestQueryRepository.php');

    assertSnapshot('TestQueryRepository.php.snapshot', $files['TestQueryRepository.php']);
});

it('can be instantiated with correct parameters', function (): void {
    $retriever = Mockery::mock(EntityRetriever::class);

    $generator = new GenerateQueryRepository(
        $retriever,
        'App\Domain\Test\Entity',
        ['TEST_TABLE']
    );

    expect($generator)->toBeInstanceOf(GenerateQueryRepository::class);
});
