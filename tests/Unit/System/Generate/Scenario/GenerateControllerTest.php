<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateController;
use Database\Schema\DbObject\Column;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;

require_once __DIR__ . '/SnapshotHelper.php';

it('generates read and write controllers correctly', function (): void {
    $tableName = 'TEST_TABLE';
    $table = new Table($tableName, []);
    $table->columns = [
        new Column('ID', 'integer', isNull: false, comment: 'id'),
        new Column('NAME', 'string', isNull: true, comment: 'name'),
        new Column('EMAIL', 'string', isNull: false, comment: 'email'),
    ];

    $retriever = Mockery::mock(EntityRetriever::class);
    $retriever->shouldReceive('getDbObject')->andReturn($table);

    $generator = new GenerateController([$tableName], $retriever, 'test');
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\Controller'));

    expect($files)->toHaveKey('ReadTestTableController.php');
    expect($files)->toHaveKey('WriteTestTableController.php');

    assertSnapshot('ReadTestTableController.php.snapshot', $files['ReadTestTableController.php']);
    assertSnapshot('WriteTestTableController.php.snapshot', $files['WriteTestTableController.php']);
});

it('can be instantiated with correct parameters', function (): void {
    $retriever = Mockery::mock(EntityRetriever::class);

    $generator = new GenerateController(
        ['TEST_TABLE'],
        $retriever,
        'test-domain'
    );

    expect($generator)->toBeInstanceOf(GenerateController::class);
});
