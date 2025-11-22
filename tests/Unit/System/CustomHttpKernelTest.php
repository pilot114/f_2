<?php

declare(strict_types=1);

namespace Tests\Unit\System;

use App\Kernel;
use App\System\RPC\RpcServer;
use Database\Connection\CpConnection;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

it('should generate all rpc spec types without exceptions', function (string $specType, string $contentType, bool $isJson): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        query: [
            'specType' => $specType,
        ],
        server: [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ]
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);
    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe($contentType);

    if ($isJson) {
        expect(json_decode($response->getContent()))->toBeObject();
    } else {
        expect($response->getContent())->toBeString();
    }
})->with([
    'openRpc' => ['openRpc', 'application/json', true],
    'jSight'  => ['jSight', 'text/text; charset=UTF-8', false],
    'postman' => ['postman', 'application/json', true],
]);

it('should handle RPC POST request', function (): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        server: [
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
            'CONTENT_TYPE'       => 'application/json',
        ],
        content: json_encode([
            'jsonrpc' => '2.0',
            'method'  => 'test.method',
            'params'  => [],
            'id'      => 1,
        ])
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);
    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/json');
});

it('should throw MethodNotAllowedHttpException for unsupported methods', function (): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        server: [
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ]
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);

    expect(fn (): Response => $kernel->handle($request))
        ->toThrow(MethodNotAllowedHttpException::class);
});

it('should generate spec with filters', function (): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        query: [
            'specType' => 'openRpc',
            'tags'     => 'hr,finance',
            'method'   => 'test.method',
        ],
        server: [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ]
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);
    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/json');
});

it('should generate spec with mocks when includeMocks is true', function (): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        query: [
            'specType'     => 'openRpc',
            'includeMocks' => '1',
        ],
        server: [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ]
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $mockConn->shouldReceive('query')->andReturn((function () {
        yield [
            'specification' => '{}',
        ];
    })());
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);
    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/json');
});

it('should use default spec type when not provided', function (): void {
    $_ENV['DB_IS_PROD'] = 'false';
    $kernel = new Kernel('test', true);
    $request = new Request(
        query: [],
        server: [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_URI'        => RpcServer::RPC_URL,
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ]
    );

    $mockConn = Mockery::mock(CpConnection::class);
    $mockConn->shouldReceive('procedure')->once();
    $kernel->boot();
    $kernel->getContainer()->set(CpConnection::class, $mockConn);
    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/json');
});
