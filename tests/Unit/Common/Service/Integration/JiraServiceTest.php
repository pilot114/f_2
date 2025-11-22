<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Integration;

use App\Common\Service\Integration\JiraClient;
use JiraRestApi\Issue\IssueSearchResult;
use JiraRestApi\Issue\IssueService;
use JsonMapper_Exception;
use Mockery;

beforeEach(function (): void {
    $this->issueService = Mockery::mock(IssueService::class);
    $this->jiraService = new JiraClient($this->issueService);
});

afterEach(function (): void {
    Mockery::close();
});

it('gets all issues by jql with pagination', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $fields = ['summary', 'status'];
    $expand = ['changelog'];

    $firstResult = Mockery::mock(IssueSearchResult::class);
    $firstResult->shouldReceive('getTotal')->andReturn(150);
    $firstResult->shouldReceive('getIssues')->andReturn(array_fill(0, 100, 'issue'));

    $secondResult = Mockery::mock(IssueSearchResult::class);
    $secondResult->shouldReceive('getIssues')->andReturn(array_fill(0, 50, 'issue'));

    $this->issueService->shouldReceive('search')
        ->with($jql)
        ->once()
        ->andReturn($firstResult);

    $this->issueService->shouldReceive('search')
        ->with($jql, 0, 100, $fields, $expand)
        ->once()
        ->andReturn($firstResult);

    $this->issueService->shouldReceive('search')
        ->with($jql, 100, 100, $fields, $expand)
        ->once()
        ->andReturn($secondResult);

    // Act
    $result = $this->jiraService->getAllIssuesByJql($jql, $fields, $expand);

    // Assert
    expect($result)->toHaveCount(150);
});

it('handles expand property exception in get all issues', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $exception = new JsonMapper_Exception('Cannot set property "expand" on class');

    $this->issueService->shouldReceive('search')
        ->with($jql)
        ->once()
        ->andThrow($exception);

    // Act
    $result = $this->jiraService->getAllIssuesByJql($jql);

    // Assert
    expect($result)->toBeEmpty();
});

it('rethrows other json mapper exceptions in get all issues', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $exception = new JsonMapper_Exception('Other error');

    $this->issueService->shouldReceive('search')
        ->with($jql)
        ->once()
        ->andThrow($exception);

    // Act & Assert
    expect(fn () => $this->jiraService->getAllIssuesByJql($jql))->toThrow(JsonMapper_Exception::class);
});

it('gets issues count', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $searchResult = Mockery::mock(IssueSearchResult::class);
    $searchResult->shouldReceive('getTotal')->andReturn(42);

    $this->issueService->shouldReceive('search')
        ->with($jql, 0, 1)
        ->once()
        ->andReturn($searchResult);

    // Act
    $result = $this->jiraService->getIssuesCount($jql);

    // Assert
    expect($result)->toBe(42);
});

it('handles expand property exception in get issues count', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $exception = new JsonMapper_Exception('Cannot set property "expand" on class');

    $this->issueService->shouldReceive('search')
        ->with($jql, 0, 1)
        ->once()
        ->andThrow($exception);

    // Act
    $result = $this->jiraService->getIssuesCount($jql);

    // Assert
    expect($result)->toBe(0);
});

it('rethrows other json mapper exceptions in get issues count', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $exception = new JsonMapper_Exception('Other error');

    $this->issueService->shouldReceive('search')
        ->with($jql, 0, 1)
        ->once()
        ->andThrow($exception);

    // Act & Assert
    expect(fn () => $this->jiraService->getIssuesCount($jql))->toThrow(JsonMapper_Exception::class);
});

it('handles single batch when total is less than limit', function (): void {
    // Arrange
    $jql = 'project = TEST';

    $result = Mockery::mock(IssueSearchResult::class);
    $result->shouldReceive('getTotal')->andReturn(50);
    $result->shouldReceive('getIssues')->andReturn(array_fill(0, 50, 'issue'));

    $this->issueService->shouldReceive('search')
        ->with($jql)
        ->once()
        ->andReturn($result);

    $this->issueService->shouldReceive('search')
        ->with($jql, 0, 100, [], [])
        ->once()
        ->andReturn($result);

    // Act
    $issues = $this->jiraService->getAllIssuesByJql($jql);

    // Assert
    expect($issues)->toHaveCount(50);
});

it('uses custom start and limit parameters', function (): void {
    // Arrange
    $jql = 'project = TEST';
    $start = 10;
    $limit = 50;

    $result = Mockery::mock(IssueSearchResult::class);
    $result->shouldReceive('getTotal')->andReturn(100);
    $result->shouldReceive('getIssues')->andReturn(array_fill(0, 50, 'issue'));

    $this->issueService->shouldReceive('search')
        ->with($jql)
        ->once()
        ->andReturn($result);

    $this->issueService->shouldReceive('search')
        ->with($jql, 10, 50, [], [])
        ->once()
        ->andReturn($result);

    $this->issueService->shouldReceive('search')
        ->with($jql, 60, 50, [], [])
        ->once()
        ->andReturn($result);

    // Act
    $issues = $this->jiraService->getAllIssuesByJql($jql, [], [], $start, $limit);

    // Assert
    expect($issues)->toHaveCount(100);
});
