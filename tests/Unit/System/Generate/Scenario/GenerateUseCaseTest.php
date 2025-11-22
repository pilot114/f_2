<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateUseCase;
use Database\Schema\DbObject\Column;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;

require_once __DIR__ . '/SnapshotHelper.php';

it('generates command use case correctly', function (): void {
    $tableName = 'TEST_TABLE';
    $table = new Table($tableName, []);
    $table->columns = [
        new Column('ID', 'integer', isNull: false, comment: 'id'),
        new Column('NAME', 'string', isNull: true, comment: 'name'),
        new Column('EMAIL', 'string', isNull: false, comment: 'email'),
    ];

    $retriever = Mockery::mock(EntityRetriever::class);
    $retriever->shouldReceive('getDbObject')->andReturn($table);

    $generator = new GenerateUseCase([$tableName], $retriever, 'App\Domain\Test\Entity');
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\UseCase'));

    expect($files)->toHaveKey('CommandTestTableUseCase.php');
    expect($files)->toHaveKey('QueryTestTableUseCase.php');

    assertSnapshot('CommandTestTableUseCase.php.snapshot', $files['CommandTestTableUseCase.php']);
    assertSnapshot('QueryTestTableUseCase.php.snapshot', $files['QueryTestTableUseCase.php']);
});
