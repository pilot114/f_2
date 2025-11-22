<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\Report;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\UseCase\GetReportUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\ReadDatabaseInterface;

beforeEach(function (): void {
    $this->reportQueryRepository = Mockery::mock(ReportQueryRepository::class);
    $this->connection = Mockery::mock(ReadDatabaseInterface::class);
    $this->access = Mockery::mock(SecurityQueryRepository::class);
    $this->useCase = new GetReportUseCase($this->reportQueryRepository, $this->connection, $this->access);

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

it('returns report from repository', function (): void {
    $reportId = 123;
    $expectedReport = new Report(
        id: $reportId,
        name: 'Test Report',
        currentUserInUk: 1
    );

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($expectedReport);

    $result = $this->useCase->getReport($reportId, $this->user);

    expect($result)->toBe($expectedReport);
    expect($result->id)->toBe($reportId);
});

it('handles different report IDs', function (): void {
    $reportId1 = 456;
    $reportId2 = 789;

    $report1 = new Report(id: $reportId1, name: 'Report 1', currentUserInUk: 1);
    $report2 = new Report(id: $reportId2, name: 'Report 2', currentUserInUk: 1);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId1)
        ->andReturn($report1);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId2)
        ->andReturn($report2);

    $result1 = $this->useCase->getReport($reportId1, $this->user);
    $result2 = $this->useCase->getReport($reportId2, $this->user);

    expect($result1->id)->toBe($reportId1);
    expect($result2->id)->toBe($reportId2);
    expect($result1)->not->toBe($result2);
});

it('returns report with complex data', function (): void {
    $reportId = 999;
    $owner = [
        'id'    => 1,
        'name'  => 'John Doe',
        'email' => 'john@example.com',
    ];
    $xmlData = '<root><DATABASENAME>test_db</DATABASENAME></root>';

    $complexReport = new Report(
        id: $reportId,
        name: 'Complex Report with XML Data',
        currentUserInUk: 5,
        data: $xmlData,
        owner: $owner
    );

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($complexReport);

    $result = $this->useCase->getReport($reportId, $this->user);

    expect($result)->toBe($complexReport);
    expect($result->id)->toBe($reportId);
});

it('handles zero and negative report IDs', function (): void {
    $reportIdZero = 0;
    $reportIdNegative = -1;

    $reportZero = new Report(id: $reportIdZero, name: 'Zero Report', currentUserInUk: 1);
    $reportNegative = new Report(id: $reportIdNegative, name: 'Negative Report', currentUserInUk: 1);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportIdZero)
        ->andReturn($reportZero);

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportIdNegative)
        ->andReturn($reportNegative);

    $resultZero = $this->useCase->getReport($reportIdZero, $this->user);
    $resultNegative = $this->useCase->getReport($reportIdNegative, $this->user);

    expect($resultZero->id)->toBe($reportIdZero);
    expect($resultNegative->id)->toBe($reportIdNegative);
});

it('delegates directly to repository', function (): void {
    $reportId = 12345;
    $report = new Report(
        id: $reportId,
        name: 'Delegation Test Report',
        currentUserInUk: 2
    );

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $result = $this->useCase->getReport($reportId, $this->user);

    expect($result)->toBe($report);
});

it('handles large report ID values', function (): void {
    $largeReportId = PHP_INT_MAX;
    $report = new Report(
        id: $largeReportId,
        name: 'Large ID Report',
        currentUserInUk: 1
    );

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($largeReportId)
        ->andReturn($report);

    $result = $this->useCase->getReport($largeReportId, $this->user);

    expect($result->id)->toBe($largeReportId);
});

it('returns Report instance', function (): void {
    $reportId = 555;
    $report = new Report(
        id: $reportId,
        name: 'Instance Test',
        currentUserInUk: 1
    );

    $this->reportQueryRepository->shouldReceive('getReport')
        ->once()
        ->with($reportId)
        ->andReturn($report);

    $result = $this->useCase->getReport($reportId, $this->user);

    expect($result)->toBeInstanceOf(Report::class);
});
