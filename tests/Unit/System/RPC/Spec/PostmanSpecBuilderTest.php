<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\Spec;

use App\Common\Attribute\RpcMethod;
use App\System\RPC\Spec\PostmanSpecBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    // Создаём фейковый генератор, вместо мокирования финального класса Generator
    $this->methodGenerator = function (array $methods) {
        foreach ($methods as $method) {
            yield $method;
        }
    };

    // Mock первого метода
    $this->method1 = Mockery::mock(RpcMethod::class);
    $this->method1->shouldReceive('isQuery')->andReturn(true);
    $this->method1->name = 'test.get.getMethod';
    $this->method1->summary = 'Тестовый метод получения данных';
    $this->method1->examples = [
        'example1' => [
            'params' => [
                'id'       => 123,
                'name'     => 'test',
                'jsonData' => '{"key":"value"}',
            ],
        ],
    ];

    // Mock второго метода
    $this->method2 = Mockery::mock(RpcMethod::class);
    $this->method2->shouldReceive('isQuery')->andReturn(false);
    $this->method2->name = 'test.create.method';
    $this->method2->summary = 'Тестовый метод создания';
    $this->method2->examples = [
        'example1' => [
            'params' => [],
        ],
    ];

    // Mock RouterInterface
    $this->router = Mockery::mock(RouterInterface::class);
    $this->router->shouldReceive('getRouteCollection')->andReturn(new RouteCollection());

    // Создаём инстанс класса со всеми зависимостями
    $this->specBuilder = new PostmanSpecBuilder(
        ($this->methodGenerator)([$this->method1, $this->method2]),
        $this->router
    );
});

it('builds base postman spec structure', function (): void {
    $result = $this->specBuilder->build();

    expect($result)
        ->toBeArray()
        ->toHaveKey('info')
        ->toHaveKey('auth')
        ->toHaveKey('variable')
        ->toHaveKey('item')
        ->and($result['info'])
        ->toHaveKey('name')
        ->toHaveKey('schema')
        ->and($result['auth'])
        ->toHaveKey('type')
        ->toHaveKey('bearer')
        ->and($result['variable'])
        ->toBeArray()
        ->toHaveCount(2);
});

it('adds spec endpoint to items', function (): void {
    $result = $this->specBuilder->build();

    expect($result['item'])
        ->toBeArray()
        ->not->toBeEmpty()
        ->and($result['item'][0])
        ->toHaveKey('name')
        ->toHaveKey('request')
        ->toHaveKey('response')
        ->and($result['item'][0]['name'])->toBe('spec')
        ->and($result['item'][0]['request']['method'])->toBe('GET')
        ->and($result['item'][0]['request']['url']['query'][0]['key'])->toBe('specType')
        ->and($result['item'][0]['request']['url']['query'][0]['value'])->toBe('postman');
});

it('adds RPC methods to items with correct type markers', function (): void {
    $result = $this->specBuilder->build();

    // Проверка первого метода (query)
    expect($result['item'][1]['name'])->toContain('[Q]')
        ->and($result['item'][1]['name'])->toContain('test.get.getMethod')
        ->and($result['item'][1]['request']['method'])->toBe('POST')
        ->and($result['item'][2]['name'])->toContain('[C]')
        ->and($result['item'][2]['name'])->toContain('test.create.method')
        ->and($result['item'][2]['request']['method'])->toBe('POST');
    // Проверка второго метода (command)
});

it('builds request example with correct format', function (): void {
    $result = $this->specBuilder->build();

    $requestJson = $result['item'][1]['request']['body']['raw'];
    $requestData = json_decode($requestJson, true);

    expect($requestData)
        ->toBeArray()
        ->toHaveKey('jsonrpc')
        ->toHaveKey('method')
        ->toHaveKey('params')
        ->toHaveKey('id')
        ->and($requestData['jsonrpc'])->toBe('2.0')
        ->and($requestData['method'])->toBe('test.get.getMethod')
        ->and($requestData['params'])->toHaveKey('id')
        ->and($requestData['params'])->toHaveKey('name')
        ->and($requestData['params'])->toHaveKey('jsonData')
        ->and($requestData['params']['jsonData'])->toBeArray();
});

it('handles json strings in params correctly', function (): void {
    $method = Mockery::mock(RpcMethod::class);
    $method->shouldReceive('isQuery')->andReturn(false);
    $method->name = 'test.method';
    $method->summary = 'Test method';
    $method->examples = [
        'example1' => [
            'params' => [
                'validJson'     => '{"data": 123, "nested": {"key": "value"}}',
                'invalidJson'   => '{not valid json}',
                'regularString' => 'simple string',
            ],
        ],
    ];

    $router = Mockery::mock(RouterInterface::class);
    $router->shouldReceive('getRouteCollection')->andReturn(new RouteCollection());
    $builder = new PostmanSpecBuilder(($this->methodGenerator)([$method]), $router);
    $result = $builder->build();

    $requestJson = $result['item'][1]['request']['body']['raw'];
    $requestData = json_decode($requestJson, true);

    expect($requestData['params']['validJson'])->toBeArray()
        ->and($requestData['params']['validJson']['data'])->toBe(123)
        ->and($requestData['params']['validJson']['nested']['key'])->toBe('value')
        ->and($requestData['params']['invalidJson'])->toBe('{not valid json}')
        ->and($requestData['params']['regularString'])->toBe('simple string');
});

it('handles empty params in examples', function (): void {
    $result = $this->specBuilder->build();

    $requestJson = $result['item'][2]['request']['body']['raw'];
    $requestData = json_decode($requestJson, true);

    expect($requestData['params'])->toBeArray()->toBeEmpty();
});

it('uses correct url format in requests', function (): void {
    $result = $this->specBuilder->build();

    // Проверка URL для методов
    expect($result['item'][1]['request']['url'])
        ->toHaveKey('raw')
        ->toHaveKey('host')
        ->toHaveKey('path')
        ->and($result['item'][1]['request']['url']['raw'])->toBe('{{domain}}/api/v2/rpc')
        ->and($result['item'][1]['request']['url']['host'])->toEqual(['{{domain}}'])
        ->and($result['item'][1]['request']['url']['path'])->toEqual(['api', 'v2', 'rpc']);
});

it('builds a complete postman collection', function (): void {
    $result = $this->specBuilder->build();

    // Проверка общей структуры коллекции
    expect($result)
        ->toHaveKeys(['info', 'auth', 'variable', 'item'])
        ->and($result['item'])->toHaveCount(3);

    // Проверка количества элементов (spec endpoint + 2 метода)

    // Проверка переменных
    $vars = array_column($result['variable'], 'key');
    expect($vars)->toContain('token')
        ->and($vars)->toContain('domain');

    // Проверка домена
    $domainVar = array_values(array_filter($result['variable'], fn ($var): bool => $var['key'] === 'domain'))[0];
    expect($domainVar['value'])->toEqual('local.portal.com');
});
