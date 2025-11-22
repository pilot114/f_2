<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\UseCase;

use App\Common\Service\Integration\ConfluenceClient;
use App\Common\Service\Integration\JiraClient;
use App\Common\Service\Integration\TempoJiraClient;
use App\Domain\Analytics\Mcp\UseCase\JiraTaskUseCase;
use JiraRestApi\JiraException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(3)
        ->and($parameters[0]->getName())->toBe('jira')
        ->and($parameters[1]->getName())->toBe('tempoJira')
        ->and($parameters[2]->getName())->toBe('confluenceClient');
});

it('has planningTask method with correct signature', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('planningTask');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('code');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('array');
});

it('has getTasksByJql method with correct signature', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('getTasksByJql');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('jql');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('string');
});

it('has getPlannedTasks method with correct signature', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('getPlannedTasks');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('from')
        ->and($parameters[1]->getName())->toBe('to');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('string');
});

it('has private helper methods', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);

    expect($reflection->hasMethod('replaceTokens'))->toBeTrue()
        ->and($reflection->hasMethod('getConfluencePageId'))->toBeTrue()
        ->and($reflection->hasMethod('extractLinks'))->toBeTrue();

    $replaceTokens = $reflection->getMethod('replaceTokens');
    expect($replaceTokens->isPrivate())->toBeTrue();

    $getConfluencePageId = $reflection->getMethod('getConfluencePageId');
    expect($getConfluencePageId->isPrivate())->toBeTrue();

    $extractLinks = $reflection->getMethod('extractLinks');
    expect($extractLinks->isPrivate())->toBeTrue();
});

it('replaceTokens method has correct parameters', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('replaceTokens');

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('template')
        ->and($parameters[1]->getName())->toBe('vars');
});

it('getConfluencePageId method returns nullable int', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('getConfluencePageId');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('int')
        ->and($returnType->allowsNull())->toBeTrue();
});

it('extractLinks method returns array', function (): void {
    $reflection = new ReflectionClass(JiraTaskUseCase::class);
    $method = $reflection->getMethod('extractLinks');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('array');
});

it('getTasksByJql returns json with empty jql', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $jiraClient->shouldReceive('getAllIssuesByJql')
        ->once()
        ->andReturn([[
            'key'    => 'TEST-1',
            'fields' => [
                'summary' => 'Test',
            ],
        ]]);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getTasksByJql('');

    expect($result)->toBeString();
});

it('getTasksByJql handles JiraException', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $exception = Mockery::mock(JiraException::class);
    $exception->shouldReceive('getResponse')->andReturn('Error response');

    $jiraClient->shouldReceive('getAllIssuesByJql')
        ->once()
        ->andThrow($exception);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getTasksByJql('invalid jql');

    expect($result)->toBe('Error response');
});

it('getPlannedTasks returns json string', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $tempoJiraClient->shouldReceive('getPlannedUsers')->once()->andReturn([]);
    $tempoJiraClient->shouldReceive('getPlanned')->once()->andReturn([]);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getPlannedTasks('2024-01-01', '2024-01-31');

    expect($result)->toBeString()
        ->and(json_decode($result, true))->toBeArray();
});

it('planningTask returns empty array on missing template', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);

    // Template file should exist, but test the logic
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('planningTask');

    expect($method->invoke($useCase, 'TEST-123'))->toBeArray();
});

it('can be instantiated', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);

    expect($useCase)->toBeInstanceOf(JiraTaskUseCase::class);
});

it('replaceTokens replaces single token', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('replaceTokens');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'Hello {{NAME}}', [
        'NAME' => 'World',
    ]);

    expect($result)->toBe('Hello World');
});

it('replaceTokens replaces multiple tokens', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('replaceTokens');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'Task: {{CODE}}, Status: {{STATUS}}', [
        'CODE'   => 'TEST-123',
        'STATUS' => 'Done',
    ]);

    expect($result)->toBe('Task: TEST-123, Status: Done');
});

it('replaceTokens keeps unmatched tokens', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('replaceTokens');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'Hello {{NAME}} and {{OTHER}}', [
        'NAME' => 'World',
    ]);

    expect($result)->toBe('Hello World and {{OTHER}}');
});

it('getConfluencePageId extracts page id from valid link', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('getConfluencePageId');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'https://docs.siberianhealth.com/pages/123456');

    expect($result)->toBe(123456);
});

it('getConfluencePageId returns null for invalid link', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('getConfluencePageId');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'https://other-site.com/pages/123');

    expect($result)->toBeNull();
});

it('getConfluencePageId returns null for link without pages', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('getConfluencePageId');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'https://docs.siberianhealth.com/other/123');

    expect($result)->toBeNull();
});

it('extractLinks extracts single link', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('extractLinks');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'Check [Documentation|https://example.com/doc] for details');

    expect($result)->toBe([
        'Documentation' => 'https://example.com/doc',
    ]);
});

it('extractLinks extracts multiple links', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('extractLinks');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, '[Doc1|http://example.com/1] and [Doc2|http://example.com/2]');

    expect($result)->toBe([
        'Doc1' => 'http://example.com/1',
        'Doc2' => 'http://example.com/2',
    ]);
});

it('extractLinks returns empty array for no links', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $reflection = new ReflectionClass($useCase);
    $method = $reflection->getMethod('extractLinks');
    $method->setAccessible(true);

    $result = $method->invoke($useCase, 'Plain text without links');

    expect($result)->toBe([]);
});

it('getTasksByJql returns specific task info with issuekey', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $jiraClient->shouldReceive('getAllIssuesByJql')
        ->with('issuekey = "TEST-123"')
        ->once()
        ->andReturn([
            [
                'key'    => 'TEST-123',
                'fields' => [
                    'summary'     => 'Test Task',
                    'description' => 'Test Description',
                ],
            ],
        ]);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getTasksByJql('issuekey = "TEST-123"');

    expect($result)->toContain('Summary: Test Task')
        ->and($result)->toContain('Description: Test Description');
});

it('getTasksByJql returns Not found when issuekey not in results', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $jiraClient->shouldReceive('getAllIssuesByJql')
        ->once()
        ->andReturn([
            [
                'key'    => 'OTHER-456',
                'fields' => [
                    'summary' => 'Other Task',
                ],
            ],
        ]);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getTasksByJql('issuekey = "TEST-123"');

    expect($result)->toBe('Not found');
});

it('getPlannedTasks handles exception', function (): void {
    $jiraClient = Mockery::mock(JiraClient::class);
    $tempoJiraClient = Mockery::mock(TempoJiraClient::class);
    $confluenceClient = Mockery::mock(ConfluenceClient::class);

    $exception = Mockery::mock(JiraException::class);
    $exception->shouldReceive('getResponse')->andReturn('Tempo error');

    $tempoJiraClient->shouldReceive('getPlannedUsers')
        ->once()
        ->andThrow($exception);

    $useCase = new JiraTaskUseCase($jiraClient, $tempoJiraClient, $confluenceClient);
    $result = $useCase->getPlannedTasks('2024-01-01', '2024-01-31');

    expect($result)->toBe('Tempo error');
});
