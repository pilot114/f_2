<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Integration;

use App\Common\Service\Integration\RpcClient;
use Database\Connection\CpConfig;
use Mockery;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

beforeEach(function (): void {
    $this->client = Mockery::mock(HttpClientInterface::class);
    $this->config = Mockery::mock(CpConfig::class);
    $this->request = Mockery::mock(Request::class);
    $this->request->headers = Mockery::mock(HeaderBag::class);
    $this->service = new RpcClient($this->client, $this->config, $this->request);
});

afterEach(function (): void {
    Mockery::close();
});

it('calls rpc method', function (): void {
    // Arrange
    $method = 'test_method';
    $params = [
        'foo' => 'bar',
    ];
    $response = Mockery::mock(ResponseInterface::class);

    $this->request->shouldReceive('getHost')->andReturn('localhost');
    $this->request->headers->shouldReceive('get')->with('Authorization')->andReturn('Bearer token');
    $this->config->isProd = false;

    $this->client->shouldReceive('request')
        ->with('POST', '/api/v1/rpc', Mockery::on(function (array $options) use ($method, $params): bool {
            return $options['base_uri'] === 'http://localhost'
                && $options['headers']['Authorization'] === 'Bearer token'
                && $options['json']['method'] === $method
                && $options['json']['params'] === $params;
        }))
        ->andReturn($response);

    $response->shouldReceive('getContent')->andReturn(json_encode([
        'result' => 'success',
    ]));

    // Act
    $result = $this->service->call($method, $params);

    // Assert
    expect($result)->toBe('success');
});
