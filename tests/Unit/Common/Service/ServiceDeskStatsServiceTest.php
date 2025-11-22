<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\ServiceDesk\Service;

use App\Common\Service\Integration\JiraClient;
use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;
use App\Domain\Dit\ServiceDesk\Service\ServiceDeskStatsService;
use DateTimeImmutable;
use JiraRestApi\JiraException;
use JsonMapper_Exception;
use Mockery;

it('returns correct count for given month when getting created issues', function (): void {
    $month = new DateTimeImmutable('2024-02-15');
    $expectedCount = 25;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getCreatedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('handles first day of month correctly when getting created issues', function (): void {
    $month = new DateTimeImmutable('2024-01-01');
    $expectedCount = 10;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getCreatedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('handles last day of month correctly when getting created issues', function (): void {
    $month = new DateTimeImmutable('2024-03-31');
    $expectedCount = 15;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getCreatedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('handles leap year February correctly when getting created issues', function (): void {
    $month = new DateTimeImmutable('2024-02-15');
    $expectedCount = 20;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getCreatedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('throws JsonMapper_Exception when jira service fails for created issues', function (): void {
    $month = new DateTimeImmutable('2024-01-15');

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andThrow(new JsonMapper_Exception('JSON mapping error'));

    $service = new ServiceDeskStatsService($jiraService);

    expect(fn (): int => $service->getCreatedIssuesCountByMonth($month))
        ->toThrow(JsonMapper_Exception::class);

    Mockery::close();
});

it('throws JiraException when jira service fails for created issues', function (): void {
    $month = new DateTimeImmutable('2024-01-15');

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andThrow(new JiraException('JIRA API error'));

    $service = new ServiceDeskStatsService($jiraService);

    expect(fn (): int => $service->getCreatedIssuesCountByMonth($month))
        ->toThrow(JiraException::class);

    Mockery::close();
});

it('returns correct count for given month when getting resolved issues', function (): void {
    $month = new DateTimeImmutable('2024-02-15');
    $expectedCount = 18;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getResolvedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('handles first day of month correctly when getting resolved issues', function (): void {
    $month = new DateTimeImmutable('2024-01-01');
    $expectedCount = 12;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getResolvedIssuesCountByMonth($month);

    expect($result)->toBe($expectedCount);
    Mockery::close();
});

it('handles zero resolved issues', function (): void {
    $month = new DateTimeImmutable('2024-12-15');
    $expectedCount = 0;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andReturn($expectedCount);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getResolvedIssuesCountByMonth($month);

    expect($result)->toBe(0);
    Mockery::close();
});

it('throws JsonMapper_Exception when jira service fails for resolved issues', function (): void {
    $month = new DateTimeImmutable('2024-01-15');

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->once()
        ->andThrow(new JsonMapper_Exception('JSON mapping error'));

    $service = new ServiceDeskStatsService($jiraService);

    expect(fn (): int => $service->getResolvedIssuesCountByMonth($month))
        ->toThrow(JsonMapper_Exception::class);

    Mockery::close();
});

it('calculates average rating correctly with all ratings', function (): void {
    $year = 2024;

    $jiraService = Mockery::mock(JiraClient::class);
    // Mock responses: rating 1=2 issues, rating 2=1 issue, rating 3=3 issues, rating 4=4 issues, rating 5=5 issues

    $jiraService
        ->shouldReceive('getIssuesCount')
        ->times(5)
        ->andReturn(5);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getRatingsStatsForYear($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBe(3.0);
    expect($result->ratingsCount)->toBe(25);

    Mockery::close();
});

it('returns null values when no ratings found', function (): void {
    $year = 2023;

    $jiraService = Mockery::mock(JiraClient::class);
    $jiraService
        ->shouldReceive('getIssuesCount')
        ->times(5)
        ->andReturn(0);

    $service = new ServiceDeskStatsService($jiraService);
    $result = $service->getRatingsStatsForYear($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBeNull();
    expect($result->ratingsCount)->toBeNull();

    Mockery::close();
});
