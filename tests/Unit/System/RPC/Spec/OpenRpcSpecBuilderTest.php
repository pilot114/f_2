<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\Spec;

use App\Common\Attribute\RpcMethod;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\OpenRpcSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenAPI\Info;
use PSX\OpenAPI\License;
use PSX\OpenAPI\Schemas;
use PSX\OpenAPI\Server;
use PSX\OpenRPC\Components;
use PSX\OpenRPC\ContentDescriptor;
use PSX\OpenRPC\Method;
use PSX\OpenRPC\OpenRPC;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    /** @var MockInterface|MockSpecRepository $this */
    $this->methods = (function () {
        if (false) {
            yield;
        }
    })();

    $this->schemas = new Schemas();
    $this->mockSpecBuilder = Mockery::mock(OpenRpcMockSpecBuilder::class);

    $this->specBuilder = new OpenRpcSpecBuilder(
        $this->methods,
        $this->schemas,
        'test'
    );

    // Создаем экземпляр для тестирования приватных методов
    $repository = Mockery::mock(MockSpecRepository::class);
    // Изменяем expects на allows, чтобы не требовать обязательного вызова
    $repository->allows('getMockSpec')->andReturns('');

    $this->builder = new OpenRpcMockSpecBuilder($this->methods, $this->schemas, 'test');
    $this->builder->setMockRepository($repository);
});

it('builds an OpenRPC spec with correct version and info', function (): void {
    // Создаем реальный Generator с пустым списком методов
    $emptyGenerator = (function () {
        if (false) {
            yield;
        }
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($emptyGenerator, $schemas, 'test');

    $result = $specBuilder->build();

    expect($result)->toBeInstanceOf(OpenRPC::class)
        ->and($result->getOpenrpc())->toBe('1.3.2')
        ->and($result->getInfo())->toBeInstanceOf(Info::class)
        ->and($result->getInfo()->getVersion())->toBe('test ' . Date('d-m-Y h:i:s'))
        ->and($result->getInfo()->getTitle())->toBe('CorPortal')
        ->and($result->getInfo()->getLicense())->toBeInstanceOf(License::class)
        ->and($result->getInfo()->getLicense()->getName())->toBe('proprietary');
});

it('builds servers correctly', function (): void {
    // Создаем реальный Generator с пустым списком методов
    $emptyGenerator = (function () {
        if (false) {
            yield;
        }
    })();

    $schemas = new Schemas();
    $specBuilder = new OpenRpcSpecBuilder($emptyGenerator, $schemas, 'test');

    $result = $specBuilder->build();
    $servers = $result->getServers();

    expect($servers)->toBeArray()->toHaveCount(3)
        ->and($servers[0])->toBeInstanceOf(Server::class)
        ->and($servers[0]->getUrl())->toBe('http://local.portal.com/api/v2/rpc')
        ->and($servers[1])->toBeInstanceOf(Server::class)
        ->and($servers[1]->getUrl())->toBe('https://beta-cp.siberianhealth.com/api/v2/rpc');
});

it('builds methods from RPC attributes', function (): void {
    // Создаем генератор с одним методом RPC
    $rpcMethod = Mockery::mock(RpcMethod::class);
    $rpcMethod->name = 'test.method.one';
    $rpcMethod->summary = 'Test method summary';
    $rpcMethod->description = 'Test method description';
    $rpcMethod->isDeprecated = false;
    $rpcMethod->tags = ['test', 'api'];
    $rpcMethod->examples = [
        'example1' => [
            'summary' => 'Example summary',
            'params'  => [
                'param1' => 'value1',
            ],
            'result' => '{"status":"success"}',
        ],
    ];
    $rpcMethod->params = [
        'param1' => (object) [
            'summary'    => 'Parameter 1',
            'required'   => true,
            'deprecated' => false,
            'schema'     => [
                'type' => 'string',
            ],
            'schemaName' => null,
        ],
    ];
    $rpcMethod->result = [
        'name'    => 'result',
        'summary' => 'Result summary',
        'schema'  => [
            'type' => 'object',
        ],
        'schemaName' => null,
    ];
    $rpcMethod->errors = [
        400 => 'Bad Request',
    ];

    $generator = (function () use ($rpcMethod) {
        yield $rpcMethod;
    })();

    $schemas = Mockery::mock(Schemas::class);
    $schemas->expects('put')->never(); // Здесь нет именованных схем

    $specBuilder = new OpenRpcSpecBuilder($generator, $schemas, 'test');

    $result = $specBuilder->build();
    $methods = $result->getMethods();

    expect($methods)->toBeArray()->toHaveCount(1)
        ->and($methods[0])->toBeInstanceOf(Method::class)
        ->and($methods[0]->getName())->toBe('test.method.one')
        ->and($methods[0]->getSummary())->toBe('Test method summary')
        ->and($methods[0]->getDescription())->toBe('Test method description')
        ->and($methods[0]->getDeprecated())->toBeFalsy()
        ->and($methods[0]->getTags())->toHaveCount(2)
        ->and($methods[0]->getExamples())->toHaveCount(1)
        ->and($methods[0]->getParams())->toHaveCount(1)
        ->and($methods[0]->getResult())->toBeInstanceOf(ContentDescriptor::class)
        ->and($methods[0]->getErrors())->toHaveCount(1);
});

it('sets components with schemas correctly', function (): void {
    // Создаем реальный Generator с пустым списком методов
    $emptyGenerator = (function () {
        if (false) {
            yield;
        }
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($emptyGenerator, $schemas, 'test');

    $result = $specBuilder->build();

    expect($result->getComponents())->toBeInstanceOf(Components::class)
        ->and($result->getComponents()->getSchemas())->toBe($schemas);
});

it('adds named schemas to repository', function (): void {
    // Создаем генератор с методом, использующим именованную схему
    $rpcMethod = Mockery::mock(RpcMethod::class);
    $rpcMethod->name = 'schema.test.method';
    $rpcMethod->summary = 'Schema test';
    $rpcMethod->description = null;
    $rpcMethod->isDeprecated = false;
    $rpcMethod->tags = [];
    $rpcMethod->examples = [];
    $rpcMethod->params = [
        'param1' => (object) [
            'summary'    => 'Parameter with schema',
            'required'   => true,
            'deprecated' => false,
            'schema'     => [
                'type'       => 'object',
                'properties' => [
                    'name' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'schemaName' => 'NamedParamSchema',
        ],
    ];

    // Исправляем структуру для result - используем resultSchema и resultSchemaName
    $rpcMethod->resultSchema = [
        'type'       => 'object',
        'properties' => [
            'id' => [
                'type' => 'integer',
            ],
        ],
    ];
    $rpcMethod->resultSchemaName = 'NamedResultSchema';
    $rpcMethod->errors = [];

    $generator = (function () use ($rpcMethod) {
        yield $rpcMethod;
    })();

    $schemas = Mockery::mock(Schemas::class);
    $schemas->expects('put')
        ->with('NamedParamSchema', [
            'type'       => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
            ],
        ])
        ->once();
    $schemas->expects('put')
        ->with('NamedResultSchema', [
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
            ],
        ])
        ->once();

    $specBuilder = new OpenRpcSpecBuilder($generator, $schemas, 'test');

    $result = $specBuilder->build();
    $method = $result->getMethods()[0];

    expect($method->getParams()[0]->getSchema())->toHaveKey('$ref')
        ->and($method->getResult()->getSchema())->toHaveKey('$ref')
        ->and($method->getResult()->getSchema()['$ref'])->toBe('#/components/schemas/NamedResultSchema');
});

it('handles method with examples correctly', function (): void {
    // Создаем генератор с методом, содержащим примеры
    $rpcMethod = Mockery::mock(RpcMethod::class);
    $rpcMethod->name = 'example.test';
    $rpcMethod->summary = 'Example test';
    $rpcMethod->description = null;
    $rpcMethod->isDeprecated = false;
    $rpcMethod->tags = [];
    $rpcMethod->examples = [
        'example1' => [
            'summary' => 'First example',
            'params'  => [
                'param1' => 'string value',
                'param2' => '{"nested":123}',
            ],
            'result' => '{"status":"ok","data":[1,2,3]}',
        ],
        'example2' => [
            'summary' => 'Second example',
            'params'  => [
                'param1' => 'another value',
            ],
            'result' => '{"status":"error"}',
        ],
    ];
    $rpcMethod->params = [];
    $rpcMethod->result = [
        'name'       => 'result',
        'summary'    => null,
        'schema'     => null,
        'schemaName' => null,
    ];
    $rpcMethod->errors = [];

    $generator = (function () use ($rpcMethod) {
        yield $rpcMethod;
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($generator, $schemas, 'test');

    $result = $specBuilder->build();
    $method = $result->getMethods()[0];
    $examples = $method->getExamples();

    expect($examples)->toHaveCount(2)
        ->and($examples[0]->getName())->toBe('example1')
        ->and($examples[0]->getSummary())->toBe('First example')
        ->and($examples[0]->getParams())->toHaveCount(2)
        ->and($examples[0]->getResult()->getValue())->toBeArray()
        ->and($examples[0]->getResult()->getValue()['status'])->toBe('ok')
        ->and($examples[1]->getName())->toBe('example2')
        ->and($examples[1]->getSummary())->toBe('Second example')
        ->and($examples[1]->getParams())->toHaveCount(1);
});

it('handles deprecated methods', function (): void {
    // Создаем генератор с устаревшим методом
    $rpcMethod = Mockery::mock(RpcMethod::class);
    $rpcMethod->name = 'deprecated.method';
    $rpcMethod->summary = 'Deprecated method';
    $rpcMethod->description = null;
    $rpcMethod->isDeprecated = true;
    $rpcMethod->tags = [];
    $rpcMethod->examples = [];
    $rpcMethod->params = [];
    $rpcMethod->resultSchema = []; // Пустой массив вместо null
    $rpcMethod->resultSchemaName = null;
    $rpcMethod->errors = [];

    $generator = (function () use ($rpcMethod) {
        yield $rpcMethod;
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($generator, $schemas, 'test');

    $result = $specBuilder->build();
    $method = $result->getMethods()[0];

    expect($method->getDeprecated())->toBeTrue();
});
it('handles multiple methods', function (): void {
    // Создаем генератор с несколькими методами
    $rpcMethod1 = Mockery::mock(RpcMethod::class);
    $rpcMethod1->name = 'method.one';
    $rpcMethod1->summary = 'Method One';
    $rpcMethod1->description = null;
    $rpcMethod1->isDeprecated = false;
    $rpcMethod1->tags = [];
    $rpcMethod1->examples = [];
    $rpcMethod1->params = [];
    $rpcMethod1->result = [
        'name'       => 'result',
        'summary'    => null,
        'schema'     => null,
        'schemaName' => null,
    ];
    $rpcMethod1->errors = [];

    $rpcMethod2 = Mockery::mock(RpcMethod::class);
    $rpcMethod2->name = 'method.two';
    $rpcMethod2->summary = 'Method Two';
    $rpcMethod2->description = null;
    $rpcMethod2->isDeprecated = false;
    $rpcMethod2->tags = [];
    $rpcMethod2->examples = [];
    $rpcMethod2->params = [];
    $rpcMethod2->result = [
        'name'       => 'result',
        'summary'    => null,
        'schema'     => null,
        'schemaName' => null,
    ];
    $rpcMethod2->errors = [];

    $generator = (function () use ($rpcMethod1, $rpcMethod2) {
        yield $rpcMethod1;
        yield $rpcMethod2;
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($generator, $schemas, 'test');

    $result = $specBuilder->build();
    $methods = $result->getMethods();

    expect($methods)->toHaveCount(2)
        ->and($methods[0]->getName())->toBe('method.one')
        ->and($methods[1]->getName())->toBe('method.two');
});

it('merges mock examples with existing methods', function (): void {
    // Создаем метод RPC с правильной структурой
    $rpcMethod = Mockery::mock(RpcMethod::class);
    $rpcMethod->name = 'shared.test.method';
    $rpcMethod->summary = 'Shared method';
    $rpcMethod->description = null;
    $rpcMethod->isDeprecated = false;
    $rpcMethod->tags = [];
    $rpcMethod->examples = [
        'original' => [
            'summary' => 'Original example',
            'params'  => [
                'param' => 'value',
            ],
            'result' => '{"source":"original"}',
        ],
    ];
    $rpcMethod->params = [];
    $rpcMethod->resultSchema = [];
    $rpcMethod->resultSchemaName = null;
    $rpcMethod->errors = [];

    $generator = (function () use ($rpcMethod) {
        yield $rpcMethod;
    })();

    $schemas = Mockery::mock(Schemas::class);

    // Создаем мок репозитория здесь, а не используем $this->repository
    $repository = Mockery::mock(MockSpecRepository::class);

    // Создаем мок-билдер который расширяет OpenRpcSpecBuilder
    $mockSpecBuilder = new OpenRpcMockSpecBuilder($generator, $schemas, 'test');
    $mockSpecBuilder->setMockRepository($repository);

    // Настраиваем мок-спецификацию с методом с тем же именем
    $mockSpec = json_encode([
        'methods' => [
            [
                'name'     => 'shared.test.method',
                'summary'  => 'Mock method',
                'examples' => [
                    [
                        'name'        => 'mockExample',
                        'description' => 'Mock example',
                        'params'      => [
                            [
                                'name'  => 'mockParam',
                                'value' => 'mockValue',
                            ],
                        ],
                        'result' => [
                            'name'  => 'mockResult',
                            'value' => [
                                'source' => 'mock',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $repository->expects('getMockSpec')
        ->andReturns($mockSpec);

    // Проверяем результат
    $result = $mockSpecBuilder->build();
    $methods = $result->getMethods();

    expect($methods)->toHaveCount(1)
        ->and($methods[0]->getName())->toBe('shared.test.method');

    $examples = $methods[0]->getExamples();
    expect($examples)->toHaveCount(2); // Оригинальный + мок

    $exampleNames = array_map(
        fn ($example): ?string => $example->getName(),
        $examples
    );

    expect($exampleNames)->toContain('original')
        ->and($exampleNames)->toContain('[MOCK] mockExample');
});

it('does nothing when mockSpec is null', function (): void {
    // Создаем генератор с пустым списком методов
    $emptyGenerator = (function () {
        if (false) {
            yield;
        }
    })();

    $schemas = Mockery::mock(Schemas::class);
    $specBuilder = new OpenRpcSpecBuilder($emptyGenerator, $schemas, 'test');

    // Проверяем результат без установки мок-спецификации
    $result = $specBuilder->build();

    expect($result->getMethods())->toBeEmpty();
});

it('creates appropriate schema for different value types', function (): void {
    // Используем рефлексию для доступа к приватному методу
    $reflectionClass = new ReflectionClass(OpenRpcMockSpecBuilder::class);
    $method = $reflectionClass->getMethod('createSchemaForValue');
    $method->setAccessible(true);

    // Тестируем различные типы значений
    $nullSchema = $method->invoke($this->builder, null);
    expect($nullSchema->type)->toBe('null');

    $boolSchema = $method->invoke($this->builder, true);
    expect($boolSchema->type)->toBe('boolean');

    $intSchema = $method->invoke($this->builder, 123);
    expect($intSchema->type)->toBe('integer');

    $floatSchema = $method->invoke($this->builder, 123.45);
    expect($floatSchema->type)->toBe('number');

    $stringSchema = $method->invoke($this->builder, 'test');
    expect($stringSchema->type)->toBe('string');

    $dateSchema = $method->invoke($this->builder, '2023-01-01');
    expect($dateSchema->type)->toBe('string')
        ->and($dateSchema->format)->toBe('date');

    $dateTimeSchema = $method->invoke($this->builder, '2023-01-01T12:00:00');
    expect($dateTimeSchema->type)->toBe('string')
        ->and($dateTimeSchema->format)->toBe('date-time');

    $emailSchema = $method->invoke($this->builder, 'test@example.com');
    expect($emailSchema->type)->toBe('string')
        ->and($emailSchema->format)->toBe('email');

    $urlSchema = $method->invoke($this->builder, 'https://example.com');
    expect($urlSchema->type)->toBe('string')
        ->and($urlSchema->format)->toBe('uri');

    $arraySchema = $method->invoke($this->builder, ['item1', 'item2']);
    expect($arraySchema->type)->toBe('array')
        ->and($arraySchema->items->type)->toBe('string');

    $objectSchema = $method->invoke($this->builder, [
        'key1' => 'value1',
        'key2' => 123,
    ]);
    expect($objectSchema->type)->toBe('object')
        ->and($objectSchema->properties->key1->type)->toBe('string')
        ->and($objectSchema->properties->key2->type)->toBe('integer')
        ->and($objectSchema->required)->toContain('key1')
        ->and($objectSchema->required)->toContain('key2');
});
