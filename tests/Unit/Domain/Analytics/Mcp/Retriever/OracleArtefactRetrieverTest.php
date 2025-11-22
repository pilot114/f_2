<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\Retriever;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\OracleArtefactRetriever;
use Database\Connection\ReadDatabaseInterface;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\DbObject\Func;
use Database\Schema\DbObject\Proc;
use Database\Schema\DbObject\Table;
use Database\Schema\DbObject\Trigger;
use Database\Schema\DbObject\View;
use Database\Schema\EntityRetriever;
use Database\Schema\Parser\Package;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->conn = Mockery::mock(ReadDatabaseInterface::class);
    $this->entityRetriever = Mockery::mock(EntityRetriever::class);
    $this->retriever = new OracleArtefactRetriever($this->conn, $this->entityRetriever);
});

it('retrieves diff for last days', function (): void {
    $generator = (function () {
        yield [
            'OBJECT_TYPE' => 'TABLE',
            'OWNER'       => 'TEST',
            'OBJECT_NAME' => 'USERS',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'DDL_HISTORY_2')),
            [
                'days' => 5,
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->getDiffForLastDays(5);

    expect($result)->toHaveCount(1)
        ->and($result[0]['OBJECT_TYPE'])->toBe('TABLE');
});

it('retrieves name list for tables', function (): void {
    $generator = (function () {
        yield [
            'owner'      => 'TEST',
            'table_name' => 'TABLE1',
        ];
        yield [
            'owner'      => 'TEST',
            'table_name' => 'TABLE2',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_TABLES')),
            []
        )
        ->andReturn($generator);

    $result = $this->retriever->getNameList(ArtefactType::TABLE);

    expect($result)->toContain('TEST.TABLE1', 'TEST.TABLE2');
});

it('retrieves name list for procedures', function (): void {
    $generator = (function () {
        yield [
            'owner'          => 'TEST',
            'object_name'    => 'PKG',
            'procedure_name' => 'PROC1',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_PROCEDURES')),
            []
        )
        ->andReturn($generator);

    $result = $this->retriever->getNameList(ArtefactType::PROCEDURE);

    expect($result)->toContain('TEST.PKG.PROC1');
});

it('retrieves name list for views', function (): void {
    $generator = (function () {
        yield [
            'owner'     => 'TEST',
            'view_name' => 'VIEW1',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_VIEWS')),
            []
        )
        ->andReturn($generator);

    $result = $this->retriever->getNameList(ArtefactType::VIEW);

    expect($result)->toContain('TEST.VIEW1');
});

it('throws exception for unsupported artefact type', function (): void {
    expect(fn () => $this->retriever->getNameList(ArtefactType::TRIGGER))
        ->toThrow(BadRequestHttpException::class);
});

it('retrieves chunk of artefacts', function (): void {
    $this->entityRetriever
        ->shouldReceive('getDbObjects')
        ->with(['test.table1', 'test.table2'], DbObjectType::Table)
        ->andReturn([
            'test.table1' => (object) [
                'name' => 'test.table1',
            ],
            'test.table2' => (object) [
                'name' => 'test.table2',
            ],
        ]);

    $result = $this->retriever->getChunk(['test.table1', 'test.table2'], ArtefactType::TABLE);

    expect($result)->toHaveCount(2);
});

it('has get method with correct signature', function (): void {
    $reflection = new ReflectionClass(OracleArtefactRetriever::class);

    expect($reflection->hasMethod('get'))->toBeTrue();

    $method = $reflection->getMethod('get');
    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('object');
});

it('has search method with correct signature', function (): void {
    $reflection = new ReflectionClass(OracleArtefactRetriever::class);

    expect($reflection->hasMethod('search'))->toBeTrue();

    $method = $reflection->getMethod('search');
    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('searches artefacts with specific type', function (): void {
    $generator = (function () {
        yield [
            'owner'      => 'TEST',
            'table_name' => 'TABLE',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->once()
        ->andReturn($generator);

    $result = $this->retriever->search('test', ArtefactType::TABLE);

    expect($result[0])->toBeArray()
        ->and($result[0][0]['type'])->toBe(ArtefactType::TABLE);
});

it('maps artefact types to db object types correctly', function (): void {
    $this->entityRetriever
        ->shouldReceive('getDbObjects')
        ->with(['test.proc'], DbObjectType::Procedure)
        ->andReturn([
            'test.proc' => (object) [],
        ]);

    $result = $this->retriever->getChunk(['test.proc'], ArtefactType::PROCEDURE);

    expect($result)->toBeArray();
});

it('gets single artefact by name and type', function (): void {
    $mockObject = Mockery::mock(Table::class);
    $mockObject->name = 'test.table1';

    $this->entityRetriever
        ->shouldReceive('getDbObject')
        ->with('test.table1', DbObjectType::Table)
        ->andReturn($mockObject);

    $result = $this->retriever->get('test.table1', ArtefactType::TABLE);

    expect($result)->toBe($mockObject);
});

it('searches all artefacts when type is null', function (): void {
    $proceduresGen = (function () {
        yield [
            'owner'          => 'TEST',
            'object_name'    => 'PKG',
            'procedure_name' => 'PROC1',
        ];
    })();

    $tablesGen = (function () {
        yield [
            'owner'      => 'TEST',
            'table_name' => 'TABLE1',
        ];
    })();

    $viewsGen = (function () {
        yield [
            'owner'     => 'TEST',
            'view_name' => 'VIEW1',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->times(3)
        ->andReturn($proceduresGen, $tablesGen, $viewsGen);

    $result = $this->retriever->search('test', null);

    expect($result[0])->toBeArray()
        ->and($result[1])->toBeInt()
        ->and($result[1])->toBeGreaterThan(0);
});

it('throws exception when getting unsupported artefact type', function (): void {
    expect(fn () => $this->retriever->get('test.trigger', ArtefactType::SCHEMA))
        ->toThrow(BadRequestHttpException::class, 'Неподдерживаемый тип артефакта');
});

it('throws exception when searching with unsupported type', function (): void {
    expect(fn () => $this->retriever->search('test', ArtefactType::PACKAGE))
        ->toThrow(BadRequestHttpException::class, 'Неподдерживаемый тип артефакта');
});

it('maps all supported artefact types to db object types', function (ArtefactType $artefactType, DbObjectType $expectedDbType, string $mockClass): void {
    $mockObject = Mockery::mock($mockClass);

    $this->entityRetriever
        ->shouldReceive('getDbObject')
        ->with('test.obj', $expectedDbType)
        ->andReturn($mockObject);

    $result = $this->retriever->get('test.obj', $artefactType);

    expect($result)->toBeInstanceOf($mockClass);
})->with([
    'procedure' => [ArtefactType::PROCEDURE, DbObjectType::Procedure, Proc::class],
    'function'  => [ArtefactType::FUNCTION, DbObjectType::Function, Func::class],
    'table'     => [ArtefactType::TABLE, DbObjectType::Table, Table::class],
    'view'      => [ArtefactType::VIEW, DbObjectType::View, View::class],
    'trigger'   => [ArtefactType::TRIGGER, DbObjectType::Trigger, Trigger::class],
    'package'   => [ArtefactType::PACKAGE, DbObjectType::Package, Package::class],
]);

it('searches procedures with query parameter', function (): void {
    $generator = (function () {
        yield [
            'owner'          => 'TEST',
            'object_name'    => 'PKG',
            'procedure_name' => 'PROC1',
        ];
        yield [
            'owner'          => 'TEST',
            'object_name'    => 'PKG',
            'procedure_name' => 'PROC2',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_PROCEDURES') && str_contains($sql, 'like')),
            [
                'q' => 'test',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('test', ArtefactType::PROCEDURE);

    expect($result[0])->toHaveCount(2)
        ->and($result[0][0]['name'])->toBe('TEST.PKG.PROC1')
        ->and($result[0][1]['name'])->toBe('TEST.PKG.PROC2');
});

it('searches tables with query parameter', function (): void {
    $generator = (function () {
        yield [
            'owner'      => 'TEST',
            'table_name' => 'USERS',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_TABLES') && str_contains($sql, 'like')),
            [
                'q' => 'users',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('users', ArtefactType::TABLE);

    expect($result[0])->toHaveCount(1)
        ->and($result[0][0]['name'])->toBe('TEST.USERS');
});

it('searches views with query parameter', function (): void {
    $generator = (function () {
        yield [
            'owner'     => 'TEST',
            'view_name' => 'V_USERS',
        ];
    })();

    $this->conn
        ->shouldReceive('query')
        ->with(
            Mockery::on(fn ($sql): bool => str_contains($sql, 'ALL_VIEWS') && str_contains($sql, 'like')),
            [
                'q' => 'users',
            ]
        )
        ->andReturn($generator);

    $result = $this->retriever->search('users', ArtefactType::VIEW);

    expect($result[0])->toHaveCount(1)
        ->and($result[0][0]['name'])->toBe('TEST.V_USERS');
});

it('gets chunk for different artefact types', function (ArtefactType $type, DbObjectType $dbType): void {
    $this->entityRetriever
        ->shouldReceive('getDbObjects')
        ->with(['test.obj1', 'test.obj2'], $dbType)
        ->andReturn([
            'test.obj1' => (object) [
                'name' => 'test.obj1',
            ],
            'test.obj2' => (object) [
                'name' => 'test.obj2',
            ],
        ]);

    $result = $this->retriever->getChunk(['test.obj1', 'test.obj2'], $type);

    expect($result)->toHaveCount(2);
})->with([
    'procedures' => [ArtefactType::PROCEDURE, DbObjectType::Procedure],
    'functions'  => [ArtefactType::FUNCTION, DbObjectType::Function],
    'tables'     => [ArtefactType::TABLE, DbObjectType::Table],
    'views'      => [ArtefactType::VIEW, DbObjectType::View],
    'triggers'   => [ArtefactType::TRIGGER, DbObjectType::Trigger],
    'packages'   => [ArtefactType::PACKAGE, DbObjectType::Package],
]);
