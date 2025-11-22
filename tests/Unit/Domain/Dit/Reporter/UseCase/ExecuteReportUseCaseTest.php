<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\Report;
use App\Domain\Dit\Reporter\Entity\ReportQuery;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Database\Connection\WriteDatabaseInterface;
use Psr\Log\LoggerInterface;

beforeEach(function (): void {
    $this->reportQueryRepository = Mockery::mock(ReportQueryRepository::class);
    $this->connection = Mockery::mock(ReadDatabaseInterface::class);
    $this->writeConnection = Mockery::mock(WriteDatabaseInterface::class);
    $this->access = Mockery::mock(SecurityQueryRepository::class);
    $this->logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
    $this->useCase = new ExecuteReportUseCase(
        $this->reportQueryRepository,
        $this->connection,
        $this->writeConnection,
        $this->access,
        $this->logger
    );

    $this->user = createSecurityUser(
        id: 123,
        email: 'testuser@example.com',
    );

    // Mock access permissions to always allow
    $this->access->shouldReceive('hasPermission')
        ->andReturn(true);
});

afterEach(function (): void {
    Mockery::close();
});

it('executes simple report without parameters', function (): void {
    $reportId = 1;
    $sql = 'SELECT * FROM test_table';

    $reportQuery = new ReportQuery(sql: $sql);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    // Mock database configuration
    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    // Mock query execution
    $expectedResult = [
        [
            'id'   => 1,
            'name' => 'Test 1',
        ],
        [
            'id'   => 2,
            'name' => 'Test 2',
        ],
    ];

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT * FROM test_table OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY', [])
        ->andReturn((function () use ($expectedResult) {
            foreach ($expectedResult as $row) {
                yield $row;
            }
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT COUNT(1) count  FROM test_table', [])
        ->andReturn((function () {
            yield [
                'count' => 2,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user);

    expect($result)->toBe([$expectedResult, 2]);
});

it('executes report with authorization required', function (): void {
    $reportId = 2;
    $sql = 'SELECT * FROM secure_table';

    $reportQuery = new ReportQuery(sql: $sql);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'secure_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    // Mock database configuration with authorization
    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'secure_db',
        ])
        ->andReturn((function () {
            yield [
                'need_authorize' => true,
                'php_name'       => 'secure_schema',
            ];
        })());

    $this->connection->shouldReceive('procedure')
        ->once()
        ->with('reporter.preporter.authorize', [
            'id' => 123,
        ]);

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = SECURE_SCHEMA');

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT * FROM secure_table OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY', [])
        ->andReturn((function () {
            if (false) {
                yield;
            }
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT COUNT(1) count  FROM secure_table', [])
        ->andReturn((function () {
            yield [
                'count' => 0,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user);

    expect($result)->toBe([[], 0]);
});

it('executes report with string parameters', function (): void {
    $reportId = 3;
    $sql = 'SELECT * FROM users WHERE name = :user_name';

    $params = [
        [
            'name'     => 'user_name',
            'dataType' => 'ftString',
        ],
    ];
    $reportQuery = new ReportQuery(sql: $sql, params: $params);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT * FROM users WHERE name = :user_name OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY', [
            'user_name' => 'John Doe',
        ])
        ->andReturn((function () {
            yield [
                'id'   => 1,
                'name' => 'John Doe',
            ];
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT COUNT(1) count  FROM users WHERE name = :user_name', [
            'user_name' => 'John Doe',
        ])
        ->andReturn((function () {
            yield [
                'count' => 1,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user, [
        'user_name' => 'John Doe',
    ]);

    expect($result)->toBe([[[
        'id'   => 1,
        'name' => 'John Doe',
    ]], 1]);
});

it('executes report with date parameters', function (): void {
    $reportId = 4;
    $sql = 'SELECT * FROM orders WHERE created_date = :order_date';

    $params = [
        [
            'name'     => 'order_date',
            'dataType' => 'ftDate',
        ],
    ];
    $reportQuery = new ReportQuery(sql: $sql, params: $params);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $expectedSql = "SELECT * FROM orders WHERE created_date = :order_date OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY";
    $this->connection->shouldReceive('query')
        ->once()
        ->with($expectedSql, [
            'order_date' => '01.01.2024',
        ])
        ->andReturn((function () {
            yield [
                'id'    => 1,
                'total' => 100,
            ];
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with("SELECT COUNT(1) count  FROM orders WHERE created_date = :order_date", [
            'order_date' => '01.01.2024',
        ])
        ->andReturn((function () {
            yield [
                'count' => 1,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user, [
        'order_date' => '01.01.2024',
    ]);

    expect($result)->toBe([[[
        'id'    => 1,
        'total' => 100,
    ]], 1]);
});

it('executes report with array parameters', function (): void {
    $reportId = 5;
    $sql = 'SELECT * FROM products WHERE category_id IN (:categories)';

    $params = [
        [
            'name'     => 'categories',
            'dataType' => 'ftArray',
        ],
    ];
    $reportQuery = new ReportQuery(sql: $sql, params: $params);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $expectedSql = "SELECT * FROM products WHERE category_id IN ('1','2','3') OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY";
    $this->connection->shouldReceive('query')
        ->once()
        ->with($expectedSql, [])
        ->andReturn((function () {
            yield [
                'id'   => 1,
                'name' => 'Product 1',
            ];
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with("SELECT COUNT(1) count  FROM products WHERE category_id IN ('1','2','3')", [])
        ->andReturn((function () {
            yield [
                'count' => 1,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user, [
        'categories' => '1,2,3',
    ]);

    expect($result)->toBe([[[
        'id'   => 1,
        'name' => 'Product 1',
    ]], 1]);
});

it('executes report with cursor parameter', function (): void {
    $reportId = 6;
    $sql = 'BEGIN get_users(:user_cursor); END;';

    $params = [
        [
            'name'     => 'user_cursor',
            'dataType' => 'ftCursor',
        ],
    ];
    $reportQuery = new ReportQuery(sql: $sql, params: $params);
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $expectedResult = [[
        'id'   => 1,
        'name' => 'User 1',
    ]];
    $this->connection->shouldReceive('queryOutCursor')
        ->once()
        ->with(
            $sql,
            'user_cursor',
            [
                'user_cursor' => null,
            ],
            [
                'user_cursor' => [ParamMode::OUT, ParamType::CURSOR],
            ]
        )
        ->andReturn($expectedResult);

    $result = $this->useCase->executeReport($reportId, $this->user);

    expect($result)->toBe([$expectedResult, 1]);
});

it('returns empty array for empty SQL', function (): void {
    $reportId = 7;

    $reportQuery = new ReportQuery(sql: '');
    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$reportQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $result = $this->useCase->executeReport($reportId, $this->user);

    expect($result)->toBe([[], 0]);
});

it('handles sub queries correctly', function (): void {
    $reportId = 8;
    $mainSql = 'SELECT * FROM main_table';
    $subSql = 'SELECT * FROM sub_table';

    $subQuery = new ReportQuery(sql: $subSql);
    $mainQuery = new ReportQuery(sql: $mainSql, sub: [$subQuery]);

    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn([
            'databaseName' => 'test_db',
            'queries'      => [$mainQuery],
        ]);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $this->connection->shouldReceive('query')
        ->once()
        ->with('select * from reporter.zz_databases where name=:name', [
            'name' => 'test_db',
        ])
        ->andReturnUsing(function () {
            yield [
                'need_authorize' => false,
                'php_name'       => 'test_schema',
            ];
        });

    $this->writeConnection->shouldReceive('command')
        ->once()
        ->with('ALTER SESSION SET CURRENT_SCHEMA = TEST_SCHEMA');

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT * FROM main_table OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY', [])
        ->andReturn((function () {
            yield [
                'id' => 1,
            ];
        })());

    $this->connection->shouldReceive('query')
        ->once()
        ->with('SELECT COUNT(1) count  FROM main_table', [])
        ->andReturn((function () {
            yield [
                'count' => 1,
            ];
        })());

    $result = $this->useCase->executeReport($reportId, $this->user);

    expect($result)->toBe([[[
        'id' => 1,
    ]], 1]);
});
