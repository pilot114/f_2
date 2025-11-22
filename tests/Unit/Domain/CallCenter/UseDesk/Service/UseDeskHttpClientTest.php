<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\CallCenter\UseDesk\Service;

use App\Domain\CallCenter\UseDesk\Service\UseDeskHttpClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->client = new UseDeskHttpClient($this->httpClient);
});

it('retrieves chats with parameters', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn(json_encode([
            'data' => ['chat1', 'chat2'],
        ]));

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            'GET',
            '/api/v1/usedesk/chats?page=1&limit=10',
            Mockery::on(fn ($options): bool => $options['base_uri'] === 'http://192.168.6.170')
        )
        ->andReturn($response);

    $result = $this->client->getChats([
        'page'  => 1,
        'limit' => 10,
    ]);

    expect($result)->toHaveKey('data')
        ->and($result['data'])->toBeArray();
});

it('retrieves chats without parameters', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn(json_encode([
            'data' => [],
        ]));

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            'GET',
            '/api/v1/usedesk/chats',
            Mockery::any()
        )
        ->andReturn($response);

    $result = $this->client->getChats([]);

    expect($result)->toBeArray();
});

it('retrieves chat messages with id and parameters', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn(json_encode([
            'messages' => ['message1', 'message2'],
        ]));

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            'GET',
            '/api/v1/usedesk/chats/123/messages?page=1',
            Mockery::on(fn ($options): bool => isset($options['headers']['Content-Type']))
        )
        ->andReturn($response);

    $result = $this->client->getChatMessages(123, [
        'page' => 1,
    ]);

    expect($result)->toHaveKey('messages')
        ->and($result['messages'])->toBeArray();
});

it('retrieves chat messages without parameters', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn(json_encode([
            'messages' => [],
        ]));

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            'GET',
            '/api/v1/usedesk/chats/456/messages',
            Mockery::any()
        )
        ->andReturn($response);

    $result = $this->client->getChatMessages(456, []);

    expect($result)->toBeArray();
});

it('sends correct headers in requests', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn('{}');

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            Mockery::any(),
            Mockery::any(),
            Mockery::on(function (array $options): bool {
                return isset($options['headers']['Content-Type'])
                    && $options['headers']['Content-Type'] === 'application/json';
            })
        )
        ->andReturn($response);

    $this->client->getChats([]);

    expect(true)->toBeTrue();
});

it('uses correct base uri', function (): void {
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn('{}');

    $this->httpClient
        ->shouldReceive('request')
        ->with(
            Mockery::any(),
            Mockery::any(),
            Mockery::on(function (array $options): bool {
                return $options['base_uri'] === 'http://192.168.6.170';
            })
        )
        ->andReturn($response);

    $this->client->getChats([]);

    expect(true)->toBeTrue();
});
