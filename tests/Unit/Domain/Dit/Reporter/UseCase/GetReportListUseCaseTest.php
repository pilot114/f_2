<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\UseCase\GetReportListUseCase;
use Database\Connection\ReadDatabaseInterface;

beforeEach(function (): void {
    $this->connection = Mockery::mock(ReadDatabaseInterface::class);
    $this->readRepo = Mockery::mock(ReportQueryRepository::class);
    $this->useCase = new GetReportListUseCase($this->connection, $this->readRepo);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns report list from repository', function (): void {
    $expectedReports = [
        [
            'id'   => 1,
            'name' => 'Report 1',
        ],
        [
            'id'   => 2,
            'name' => 'Report 2',
        ],
        [
            'id'   => 3,
            'name' => 'Report 3',
        ],
    ];

    $this->readRepo->shouldReceive('getReportList')
        ->once()
        ->andReturn($expectedReports);

    $result = $this->useCase->getReportList();

    expect($result)->toBe($expectedReports);
});

it('returns empty array when no reports exist', function (): void {
    $this->readRepo->shouldReceive('getReportList')
        ->once()
        ->andReturn([]);

    $result = $this->useCase->getReportList();

    expect($result)->toBe([]);
});

it('handles large number of reports', function (): void {
    $largeReportList = [];
    for ($i = 1; $i <= 1000; $i++) {
        $largeReportList[] = [
            'id'         => $i,
            'name'       => "Report $i",
            'created_at' => "2024-01-$i",
        ];
    }

    $this->readRepo->shouldReceive('getReportList')
        ->once()
        ->andReturn($largeReportList);

    $result = $this->useCase->getReportList();

    expect($result)->toHaveCount(1000);
    expect($result[0]['id'])->toBe(1);
    expect($result[999]['id'])->toBe(1000);
});

it('returns complex report data structure', function (): void {
    $complexReports = [
        [
            'id'          => 100,
            'name'        => 'Sales Report',
            'description' => 'Monthly sales analysis',
            'category'    => 'Financial',
            'owner'       => [
                'id'    => 1,
                'name'  => 'John Manager',
                'email' => 'john@company.com',
            ],
            'metadata' => [
                'last_run'         => '2024-01-15 10:30:00',
                'execution_time'   => 45.7,
                'parameters_count' => 5,
            ],
        ],
        [
            'id'          => 200,
            'name'        => 'User Activity Report',
            'description' => 'User engagement metrics',
            'category'    => 'Analytics',
            'owner'       => [
                'id'    => 2,
                'name'  => 'Jane Analyst',
                'email' => 'jane@company.com',
            ],
            'metadata' => [
                'last_run'         => '2024-01-14 09:15:00',
                'execution_time'   => 23.1,
                'parameters_count' => 3,
            ],
        ],
    ];

    $this->readRepo->shouldReceive('getReportList')
        ->once()
        ->andReturn($complexReports);

    $result = $this->useCase->getReportList();

    expect($result)->toHaveCount(2);
    expect($result[0]['name'])->toBe('Sales Report');
    expect($result[0]['owner']['name'])->toBe('John Manager');
    expect($result[1]['metadata']['execution_time'])->toBe(23.1);
});

it('delegates to repository without modification', function (): void {
    $reportData = [
        [
            'id'     => 42,
            'name'   => 'Test Report',
            'status' => 'active',
        ],
    ];

    $this->readRepo->shouldReceive('getReportList')
        ->once()
        ->andReturn($reportData);

    $result = $this->useCase->getReportList();

    expect($result)->toBe($reportData);
});
