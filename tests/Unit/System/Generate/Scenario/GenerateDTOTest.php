<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateDTO;
use Database\Schema\DbObject\Column;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;

require_once __DIR__ . '/SnapshotHelper.php';

it('can be instantiated with correct parameters', function (): void {
    $retriever = Mockery::mock(EntityRetriever::class);

    $generator = new GenerateDTO(
        ['TEST_TABLE'],
        $retriever,
        'App\Domain\Test\Entity'
    );

    expect($generator)->toBeInstanceOf(GenerateDTO::class);
});

it('loads tables correctly', function (): void {
    $tableName = 'TEST_TABLE';
    $table = new Table($tableName, []);
    $table->columns = [
        new Column('ID', 'integer', isNull: false, comment: 'id'),
        new Column('NAME', 'string', isNull: true, comment: 'name'),
    ];

    $retriever = Mockery::mock(EntityRetriever::class);
    $retriever->shouldReceive('getDbObject')->andReturn($table);

    $generator = new GenerateDTO([$tableName], $retriever, 'App\Domain\Test\Entity');

    // The load method should work without errors
    $generator->load();

    expect($generator)->toBeInstanceOf(GenerateDTO::class);
});
