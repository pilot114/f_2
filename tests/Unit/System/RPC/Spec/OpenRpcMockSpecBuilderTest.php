<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\Spec;

use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenAPI\Schemas;
use PSX\OpenRPC\OpenRPC;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    /** @var MockInterface|MockSpecRepository $this */
    $this->repository = Mockery::mock(MockSpecRepository::class);

    // Создаем пустой генератор для методов
    $this->methods = (function () {
        if (false) {
            yield;
        }
    })();

    // Создаем мок для схем
    $this->schemas = new Schemas();

    // Создаем билдер с нужными параметрами
    $this->builder = new OpenRpcMockSpecBuilder($this->methods, $this->schemas, 'test');
    $this->builder->setMockRepository($this->repository);
});

it('builds empty OpenRPC when no mock spec is available', function (): void {
    $this->repository->expects('getMockSpec')
        ->andReturns('');

    $openRPC = $this->builder->build();

    expect($openRPC)->toBeInstanceOf(OpenRPC::class)
        ->and($openRPC->getMethods())->toBeArray()
        ->and($openRPC->getMethods())->toBeEmpty();
});

it('builds OpenRPC from valid mock spec', function (): void {
    $mockSpec = json_encode([
        'methods' => [
            [
                'name'     => 'test.method',
                'summary'  => 'Test method summary',
                'examples' => [
                    [
                        'name'        => '[MOCK] Example 1',
                        'description' => 'Test example',
                        'params'      => [
                            [
                                'name'  => 'param1',
                                'value' => 'value1',
                            ],
                        ],
                        'result' => [
                            'name'  => 'Result',
                            'value' => [
                                'status' => 'success',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repository->expects('getMockSpec')
        ->andReturns($mockSpec);

    $openRPC = $this->builder->build();

    expect($openRPC)->toBeInstanceOf(OpenRPC::class)
        ->and($openRPC->getMethods())->toBeArray()
        ->and(count($openRPC->getMethods()))->toBe(1)
        ->and($openRPC->getMethods()[0]->getName())->toBe('test.method')
        ->and($openRPC->getMethods()[0]->getSummary())->toBe('Test method summary');
});

it('adds mock to existing method', function (): void {
    // Сначала создаем пустую OpenRPC
    $this->repository->expects('getMockSpec')
        ->andReturns('{"methods":[]}');

    // Затем ожидаем, что метод saveMockSpec будет вызван с новой OpenRPC
    $this->repository->expects('saveMockSpec')
        ->andReturnUsing(function (OpenRPC $spec): true {
            $methods = $spec->getMethods();
            expect($methods)->toBeArray()
                ->and(count($methods))->toBe(1)
                ->and($methods[0]->getName())->toBe('test.method');
            return true;
        });

    // Добавляем мок
    $result = $this->builder->addMock('test.method', [
        'status' => 'success',
    ], [
        'param1' => 'value1',
    ]);

    expect($result)->toBeTrue();
});

it('removes mock by method name', function (): void {
    // Подготавливаем мок-спецификацию с одним методом
    $mockSpec = json_encode([
        'methods' => [
            [
                'name'     => 'test.method',
                'examples' => [
                    [
                        'name'   => 'test.method',
                        'params' => [
                            [
                                'name'  => 'param1',
                                'value' => 'value1',
                            ],
                        ],
                        'result' => [
                            'name'  => 'Result',
                            'value' => [
                                'status' => 'success',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repository->expects('getMockSpec')
        ->andReturns($mockSpec);

    $this->repository->expects('saveMockSpec')
        ->andReturnUsing(function (OpenRPC $spec): true {
            expect($spec->getMethods())->toBeArray()
                ->and(count($spec->getMethods()))->toBe(0);
            return true;
        });

    $result = $this->builder->removeMock('test.method');

    expect($result)->toBeTrue();
});

it('removes mock by method name and params', function (): void {
    // Подготавливаем мок-спецификацию с одним методом и двумя примерами
    $mockSpec = json_encode([
        'methods' => [
            [
                'name'     => 'test.method',
                'examples' => [
                    [
                        'name'   => 'Example 1',
                        'params' => [
                            [
                                'name'  => 'param1',
                                'value' => 'value1',
                            ],
                        ],
                        'result' => [
                            'name'  => 'Result 1',
                            'value' => [
                                'status' => 'success1',
                            ],
                        ],
                    ],
                    [
                        'name'   => 'Example 2',
                        'params' => [
                            [
                                'name'  => 'param1',
                                'value' => 'value2',
                            ],
                        ],
                        'result' => [
                            'name'  => 'Result 2',
                            'value' => [
                                'status' => 'success2',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repository->expects('getMockSpec')
        ->andReturns($mockSpec);

    $this->repository->expects('saveMockSpec')
        ->andReturnUsing(function (OpenRPC $spec): true {
            $methods = $spec->getMethods();
            expect($methods)->toBeArray()
                ->and(count($methods))->toBe(1);

            $examples = $methods[0]->getExamples();
            expect($examples)->toBeArray()
                ->and(count($examples))->toBe(1)
                ->and($examples[0]->getName())->toBe('[MOCK] Example 2');

            return true;
        });

    // Удаляем пример с param1=value1
    $result = $this->builder->removeMock('test.method', [
        'param1' => 'value1',
    ]);

    expect($result)->toBeTrue();
});

it('throws exception when trying to remove non-existent method', function (): void {
    $this->repository->expects('getMockSpec')
        ->andReturns('{"methods":[]}');

    expect(fn () => $this->builder->removeMock('non_existent_method'))
        ->toThrow(Exception::class, 'Метод для удаления не найден');
});

it('throws exception when trying to remove non-existent example', function (): void {
    $mockSpec = json_encode([
        'methods' => [
            [
                'name'     => 'test.method',
                'examples' => [
                    [
                        'name'   => 'Example',
                        'params' => [
                            [
                                'name'  => 'param1',
                                'value' => 'value1',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repository->expects('getMockSpec')
        ->andReturns($mockSpec);

    // Пытаемся удалить пример с несуществующими параметрами
    expect(fn () => $this->builder->removeMock('test.method', [
        'param2' => 'value2',
    ]))
        ->toThrow(Exception::class, 'Пример для удаления не найден');
});

it('creates appropriate schema for different value types', function (): void {
    // Используем рефлексию для доступа к приватному методу
    $reflectionClass = new ReflectionClass(OpenRpcMockSpecBuilder::class);
    $method = $reflectionClass->getMethod('createSchemaForValue');

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

it('sets mock JSON spec correctly', function (): void {
    $mockSpec = '{"methods":[{"name":"test.method"}]}';
    $this->builder->setMockJsonSpec($mockSpec);

    // Проверяем, что приватное свойство установлено корректно
    $reflectionClass = new ReflectionClass($this->builder);
    $property = $reflectionClass->getProperty('jsonMockSpec');
    $property->setAccessible(true);

    expect($property->getValue($this->builder))->toBe($mockSpec);

    // Репозиторий не должен вызываться, так как мы установили jsonMockSpec
    $this->repository->allows('getMockSpec')->never();

    // Проверяем через рефлексию, что getMockSpec возвращает правильную спецификацию
    $method = $reflectionClass->getMethod('getMockSpec');
    $method->setAccessible(true);

    expect($method->invoke($this->builder))->toBe($mockSpec);
});
