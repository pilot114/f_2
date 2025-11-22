<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command\MockManagement;

use App\System\Command\MockManagement\MockListCommand;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenAPI\Schemas;
use PSX\OpenRPC\ExampleObject;
use PSX\OpenRPC\ExamplePairingObject;
use PSX\OpenRPC\Method;
use PSX\OpenRPC\OpenRPC;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    /** @var MockInterface|RpcMethodLoader $loader */
    $loader = Mockery::mock(RpcMethodLoader::class);
    $loader->allows('getSchemas')->andReturns(new Schemas());

    // Возвращаем пустой генератор
    $loader->allows('load')->andReturns((function () {
        if (false) {
            yield;
        }
    })());

    $this->command = new MockListCommand($loader);
    $this->commandTester = new CommandTester($this->command);

    // Создаем реальный экземпляр OpenRpcMockSpecBuilder, а не мок
    $emptyGenerator = (function () {
        if (false) {
            yield;
        }
    })();

    $schemas = new Schemas();
    $realMockService = new OpenRpcMockSpecBuilder($emptyGenerator, $schemas, 'test');

    // Создаем мок репозитория
    /** @var MockInterface|MockSpecRepository $repository */
    $repository = Mockery::mock(MockSpecRepository::class);
    $repository->allows('getMockSpec')->andReturns('');

    $realMockService->setMockRepository($repository);

    // Создаем частичный мок, который позволяет переопределить только метод build()
    /** @var MockInterface|OpenRpcMockSpecBuilder $mockService */
    $this->mockService = Mockery::mock($realMockService)->makePartial();

    // Заменяем реальный сервис на наш частичный мок
    $reflection = new ReflectionClass($this->command);
    $property = $reflection->getProperty('mockService');
    $property->setAccessible(true);
    $property->setValue($this->command, $this->mockService);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock:list')
        ->and($this->command->getDescription())->toBe('Показать список всех мок-ответов');
});

it('displays message when no mocks are found', function (): void {
    $openRPC = new OpenRPC();

    $method = new Method();
    $method->setName('test.method');

    $openRPC->setMethods([$method]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Моки не найдены');
});

it('displays mocks in a table format', function (): void {
    $openRPC = new OpenRPC();

    // Создаем метод с примером
    $method = new Method();
    $method->setName('test.method');

    $example = new ExamplePairingObject();
    $example->setName('Example 1');

    // Параметры для примера
    $param1 = new ExampleObject();
    $param1->setName('param1');
    $param1->setValue('value1');

    $param2 = new ExampleObject();
    $param2->setName('param2');
    $param2->setValue(123);

    $example->setParams([$param1, $param2]);

    // Результат для примера
    $result = new ExampleObject();
    $result->setName('result');
    $result->setValue([
        'status' => 'success',
    ]);
    $example->setResult($result);

    // Добавляем пример к методу
    $method->setExamples([$example]);

    // Добавляем метод к спецификации
    $openRPC->setMethods([$method]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    $output = $this->commandTester->getDisplay();

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain('test.method')
        ->and($output)->toContain('Example 1')
        ->and($output)->toContain('param1')
        ->and($output)->toContain('value1')
        ->and($output)->toContain('param2')
        ->and($output)->toContain('123')
        ->and($output)->toContain('status')
        ->and($output)->toContain('success');
});

it('handles methods without examples', function (): void {
    $openRPC = new OpenRPC();

    // Создаем метод без примеров
    $method = new Method();
    $method->setName('test.method.no.examples');

    // Добавляем метод к спецификации
    $openRPC->setMethods([$method]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Моки не найдены');
});

it('displays multiple methods and examples', function (): void {
    $openRPC = new OpenRPC();

    // Создаем первый метод с примером
    $method1 = new Method();
    $method1->setName('test.method1');

    $example1 = new ExamplePairingObject();
    $example1->setName('Example 1');

    $param1 = new ExampleObject();
    $param1->setName('param1');
    $param1->setValue('value1');
    $example1->setParams([$param1]);

    $result1 = new ExampleObject();
    $result1->setName('result1');
    $result1->setValue('success1');
    $example1->setResult($result1);

    $method1->setExamples([$example1]);

    // Создаем второй метод с примером
    $method2 = new Method();
    $method2->setName('test.method2');

    $example2 = new ExamplePairingObject();
    $example2->setName('Example 2');

    $param2 = new ExampleObject();
    $param2->setName('param2');
    $param2->setValue('value2');
    $example2->setParams([$param2]);

    $result2 = new ExampleObject();
    $result2->setName('result2');
    $result2->setValue('success2');
    $example2->setResult($result2);

    $method2->setExamples([$example2]);

    // Добавляем методы к спецификации
    $openRPC->setMethods([$method1, $method2]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    $output = $this->commandTester->getDisplay();

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain('test.method1')
        ->and($output)->toContain('Example 1')
        ->and($output)->toContain('param1')
        ->and($output)->toContain('value1')
        ->and($output)->toContain('success1')
        ->and($output)->toContain('test.method2')
        ->and($output)->toContain('Example 2')
        ->and($output)->toContain('param2')
        ->and($output)->toContain('value2')
        ->and($output)->toContain('success2');
});

it('handles examples without params', function (): void {
    $openRPC = new OpenRPC();

    $method = new Method();
    $method->setName('test.method.no.params');

    $example = new ExamplePairingObject();
    $example->setName('Example No Params');

    $result = new ExampleObject();
    $result->setName('result');
    $result->setValue('success');
    $example->setResult($result);

    $method->setExamples([$example]);
    $openRPC->setMethods([$method]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    $output = $this->commandTester->getDisplay();

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain('test.method.no.params')
        ->and($output)->toContain('Example No Params')
        ->and($output)->toContain('-') // Для отсутствующих параметров отображается "-"
        ->and($output)->toContain('success');
});

it('handles examples without result', function (): void {
    $openRPC = new OpenRPC();

    $method = new Method();
    $method->setName('test.method.no.result');

    $example = new ExamplePairingObject();
    $example->setName('Example No Result');

    $param = new ExampleObject();
    $param->setName('param');
    $param->setValue('value');
    $example->setParams([$param]);

    $method->setExamples([$example]);
    $openRPC->setMethods([$method]);

    $this->mockService->expects('build')
        ->andReturns($openRPC);

    $this->commandTester->execute([]);

    $output = $this->commandTester->getDisplay();

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain('test.method.no.result')
        ->and($output)->toContain('Example No Result')
        ->and($output)->toContain('param')
        ->and($output)->toContain('value')
        ->and($output)->toContain('-'); // Для отсутствующего результата отображается "-"
});
