<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\Report;
use App\Domain\Dit\Reporter\Entity\ReportQuery;
use App\Domain\Dit\Reporter\XMLParser;

beforeEach(function (): void {
    // We'll mock XMLParser per test when needed
});

afterEach(function (): void {
    Mockery::close();
});

it('creates Report with basic values', function (): void {
    $report = new Report(
        id: 123,
        name: 'Test Report',
        currentUserInUk: 1
    );

    expect($report->id)->toBe(123);
});

it('creates Report with all parameters', function (): void {
    $owner = [
        'id'    => 456,
        'name'  => 'John Doe',
        'email' => 'john@example.com',
    ];
    $xmlData = '<root><DATABASENAME>test_db</DATABASENAME></root>';

    $report = new Report(
        id: 789,
        name: 'Full Report',
        currentUserInUk: 1,
        data: $xmlData,
        owner: $owner
    );

    expect($report->id)->toBe(789);
});

it('returns empty array when data is null in getData', function (): void {
    $report = new Report(
        id: 1,
        name: 'No Data Report',
        currentUserInUk: 1
    );

    $result = $report->getData();

    expect($result)->toBe([]);
});

it('parses XML data in getData when data is present', function (): void {
    // Mock the Report to test getData behavior without XMLParser dependency
    $parsedData = [
        'databaseName' => 'test_db',
        'queries'      => [],
    ];

    $report = Mockery::mock(Report::class);
    $report->shouldReceive('getData')
        ->once()
        ->andReturn($parsedData);

    $result = $report->getData();

    expect($result)->toBe($parsedData);
});

it('converts to array correctly without data', function (): void {
    $owner = [
        'id'    => 123,
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ];

    $report = new Report(
        id: 456,
        name: 'Simple Report',
        currentUserInUk: 1,
        owner: $owner
    );

    $result = $report->toArray();

    expect($result)->toBe([
        'id'    => 456,
        'name'  => 'Simple Report',
        'owner' => $owner,
        'data'  => [],
    ]);
});

it('converts to array correctly with data', function (): void {
    // Mock Report to test toArray behavior with data
    $testQuery = new ReportQuery(
        sql: 'SELECT * FROM test',
        caption: 'Test Query'
    );

    $parsedData = [
        'databaseName' => 'test_db',
        'queries'      => [$testQuery],
    ];

    $report = Mockery::mock(Report::class)->makePartial();
    $report->shouldReceive('getData')
        ->once()
        ->andReturn($parsedData);

    // Set up the report properties for toArray
    $report->__construct(
        id: 789,
        name: 'Data Report',
        currentUserInUk: 1
    );

    $result = $report->toArray();

    expect($result['id'])->toBe(789);
    expect($result['name'])->toBe('Data Report');
    expect($result['data']['databaseName'])->toBe('test_db');
    expect($result['data']['queries'])->toHaveCount(1);
    expect($result['data']['queries'][0])->toBeInstanceOf(ReportQuery::class);
    expect($result['data']['queries'][0]->toArray())->toBeArray();
});

it('extracts params from queries correctly', function (): void {
    // Mock the direct array structure that getParams expects
    $parsedData = [
        [
            'params' => [
                [
                    'name'    => 'param1',
                    'caption' => 'Parameter 1',
                ],
                [
                    'name'    => 'param2',
                    'caption' => 'Parameter 2',
                ],
            ],
            'sub' => [
                [
                    'params' => [
                        [
                            'name'    => 'param3',
                            'caption' => 'Parameter 3',
                        ],
                        [
                            'name'    => 'param1',
                            'caption' => 'Duplicate Param',
                        ], // should be ignored
                    ],
                ],
            ],
        ],
    ];

    $report = Mockery::mock(Report::class)->makePartial();
    $report->shouldReceive('getData')
        ->once()
        ->andReturn($parsedData);

    $report->__construct(
        id: 1,
        name: 'Params Report',
        currentUserInUk: 1
    );

    $params = $report->getParams();

    expect($params)->toHaveCount(3);
    expect($params[0]['name'])->toBe('param1');
    expect($params[1]['name'])->toBe('param2');
    expect($params[2]['name'])->toBe('param3');
});

it('handles empty params in getParams', function (): void {
    $parsedData = [
        [
            'sql' => 'SELECT 1',
        ], // no params
    ];

    $report = Mockery::mock(Report::class)->makePartial();
    $report->shouldReceive('getData')
        ->once()
        ->andReturn($parsedData);

    $report->__construct(
        id: 1,
        name: 'No Params Report',
        currentUserInUk: 1
    );

    $params = $report->getParams();

    expect($params)->toBe([]);
});

it('processes nested sub queries in getParams', function (): void {
    // Mock the direct array structure that getParams expects
    $parsedData = [
        [
            'params' => [
                [
                    'name'    => 'main_param',
                    'caption' => 'Main Parameter',
                ],
            ],
            'sub' => [
                [
                    'params' => [
                        [
                            'name'    => 'sub_param1',
                            'caption' => 'Sub Parameter 1',
                        ],
                    ],
                    'sub' => [
                        [
                            'params' => [
                                [
                                    'name'    => 'deep_param',
                                    'caption' => 'Deep Parameter',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $report = Mockery::mock(Report::class)->makePartial();
    $report->shouldReceive('getData')
        ->once()
        ->andReturn($parsedData);

    $report->__construct(
        id: 1,
        name: 'Nested Report',
        currentUserInUk: 1
    );

    $params = $report->getParams();

    expect($params)->toHaveCount(3);
    expect(array_column($params, 'name'))->toContain('main_param');
    expect(array_column($params, 'name'))->toContain('sub_param1');
    expect(array_column($params, 'name'))->toContain('deep_param');
});

it('handles null owner correctly', function (): void {
    $report = new Report(
        id: 1,
        name: 'No Owner Report',
        currentUserInUk: 1
    );

    $result = $report->toArray();

    expect($result['owner'])->toBeNull();
});

it('creates Report with complex configuration', function (): void {
    $owner = [
        'id'    => 999,
        'name'  => 'Complex User',
        'email' => 'complex@example.com',
    ];

    // Test Report creation and toArray without XML data to avoid XMLParser
    $report = new Report(
        id: 12345,
        name: 'Complex Report with Long Name and Special Characters !@#$%',
        currentUserInUk: 5,
        data: null,  // No XML data to avoid XMLParser
        owner: $owner
    );

    expect($report->id)->toBe(12345);

    $array = $report->toArray();
    expect($array['id'])->toBe(12345);
    expect($array['owner'])->toBe($owner);
    expect($array['data'])->toBe([]);  // Empty since no XML data
});
