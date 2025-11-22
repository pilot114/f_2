<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateEntity;
use Database\Schema\DbObject\Column;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;

require_once __DIR__ . '/SnapshotHelper.php';

it('generates entity correctly', function (): void {
    $tableName = 'TEST_TABLE';
    $table = new Table($tableName, []);
    $table->columns = [
        new Column('ID', 'integer', isNull: false, comment: 'id'),
        new Column('NAME', 'string', isNull: true, comment: 'name'),
    ];

    $retriever = Mockery::mock(EntityRetriever::class);
    $retriever->shouldReceive('getDbObject')->andReturn($table);

    $generator = new GenerateEntity([$tableName], $retriever);
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\Entity'));

    expect($files)->toHaveKey('TestTable.php');

    assertSnapshot('TestTable.php.snapshot', $files['TestTable.php']);
});
