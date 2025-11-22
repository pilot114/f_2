<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Integration;

use App\Common\Service\Integration\TempoJiraClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->client = new TempoJiraClient($this->httpClient);
});

afterEach(function (): void {
    Mockery::close();
});

it('имеет зависимость от HttpClientInterface', function (): void {
    $reflection = new ReflectionClass(TempoJiraClient::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('client')
        ->and($parameters[0]->getType()?->getName())->toBe('Symfony\Contracts\HttpClient\HttpClientInterface');
});

it('имеет метод getPlanned', function (): void {
    $reflection = new ReflectionClass(TempoJiraClient::class);

    expect($reflection->hasMethod('getPlanned'))->toBeTrue();

    $method = $reflection->getMethod('getPlanned');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('имеет метод getPlannedUsers', function (): void {
    $reflection = new ReflectionClass(TempoJiraClient::class);

    expect($reflection->hasMethod('getPlannedUsers'))->toBeTrue();

    $method = $reflection->getMethod('getPlannedUsers');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('выполняет POST запрос в getPlanned', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('toArray')->andReturns([
        'data' => 'test',
    ]);

    $this->httpClient
        ->expects('request')
        ->with('POST', Mockery::type('string'), Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getPlanned();

    expect($result)->toBeArray()
        ->toHaveKey('data');
});

it('выполняет POST запрос в getPlannedUsers', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns('{"users": []}');
    $response->expects('getStatusCode')->andReturns(200);

    $this->httpClient
        ->expects('request')
        ->with('POST', Mockery::type('string'), Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getPlannedUsers();

    expect($result)->toBeArray()
        ->toHaveKey('status')
        ->toHaveKey('body');
});
