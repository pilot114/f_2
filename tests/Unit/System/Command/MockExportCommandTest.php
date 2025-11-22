<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command\MockManagement;

use App\System\Command\MockManagement\MockExportCommand;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
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

    $this->command = new MockExportCommand($loader);
    $this->commandTester = new CommandTester($this->command);

    // Создаем мок для OpenRpcMockSpecBuilder
    /** @var MockInterface|OpenRpcMockSpecBuilder $mockService */
    $this->mockService = Mockery::mock(OpenRpcMockSpecBuilder::class);

    // Заменяем реальный сервис на мок через рефлексию
    $reflection = new ReflectionClass($this->command);
    $property = $reflection->getProperty('mockService');
    $property->setAccessible(true);
    $property->setValue($this->command, $this->mockService);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock:export')
        ->and($this->command->getDescription())->toBe('Экспортировать мок-ответы в файл');
});

it('exports mocks to file successfully', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_export_test_');

    // Создаем тестовые данные OpenRPC
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.3.2');

    // Создаем метод с примером
    $method = new Method();
    $method->setName('test.method');
    $method->setSummary('Test method summary');

    // Создаем пример
    $example = new ExamplePairingObject();
    $example->setName('Test example');
    $example->setDescription('Test example description');

    // Создаем параметры для примера
    $param = new ExampleObject();
    $param->setName('testParam');
    $param->setValue('testValue');
    $example->setParams([$param]);

    // Создаем результат для примера
    $result = new ExampleObject();
    $result->setName('result');
    $result->setValue([
        'status' => 'success',
    ]);
    $example->setResult($result);

    $method->setExamples([$example]);
    $openRPC->setMethods([$method]);

    // Настраиваем мок
    $this->mockService->expects('build')
        ->once()
        ->andReturns($openRPC);

    // Выполняем команду
    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    // Проверяем результат выполнения
    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Спецификация моков экспортирована в файл');

    // Проверяем, что файл создан
    expect(file_exists($tempFile))->toBeTrue();

    // Проверяем содержимое файла
    $exportedData = json_decode(file_get_contents($tempFile), true);
    expect($exportedData)->toBeArray()
        ->and($exportedData['openrpc'])->toBe('1.3.2')
        ->and($exportedData['methods'])->toBeArray()
        ->and($exportedData['methods'])->toHaveCount(1)
        ->and($exportedData['methods'][0]['name'])->toBe('test.method')
        ->and($exportedData['methods'][0]['summary'])->toBe('Test method summary')
        ->and($exportedData['methods'][0]['examples'])->toBeArray()
        ->and($exportedData['methods'][0]['examples'])->toHaveCount(1)
        ->and($exportedData['methods'][0]['examples'][0]['name'])->toBe('Test example')
        ->and($exportedData['methods'][0]['examples'][0]['description'])->toBe('Test example description')
        ->and($exportedData['methods'][0]['examples'][0]['params'])->toBeArray()
        ->and($exportedData['methods'][0]['examples'][0]['params'])->toHaveCount(1)
        ->and($exportedData['methods'][0]['examples'][0]['params'][0]['name'])->toBe('testParam')
        ->and($exportedData['methods'][0]['examples'][0]['params'][0]['value'])->toBe('testValue')
        ->and($exportedData['methods'][0]['examples'][0]['result']['name'])->toBe('result')
        ->and($exportedData['methods'][0]['examples'][0]['result']['value']['status'])->toBe('success');

    // Удаляем временный файл
    unlink($tempFile);
});

it('handles empty methods correctly', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_export_test_empty_');

    // Создаем пустой OpenRPC
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.3.2');
    $openRPC->setMethods([]);

    // Настраиваем мок
    $this->mockService->expects('build')
        ->once()
        ->andReturns($openRPC);

    // Выполняем команду
    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    // Проверяем результат выполнения
    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);

    // Проверяем содержимое файла
    $exportedData = json_decode(file_get_contents($tempFile), true);
    expect($exportedData)->toBeArray()
        ->and($exportedData['openrpc'])->toBe('1.3.2')
        ->and($exportedData['methods'])->toBeArray()
        ->and($exportedData['methods'])->toHaveCount(0);

    // Удаляем временный файл
    unlink($tempFile);
});

it('fails when directory does not exist', function (): void {
    $invalidFile = '/non/existent/directory/test.json';

    // Создаем пустой OpenRPC
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.3.2');

    // Настраиваем мок
    $this->mockService->expects('build')
        ->once()
        ->andReturns($openRPC);

    // Выполняем команду
    $this->commandTester->execute([
        'file' => $invalidFile,
    ]);

    // Проверяем результат выполнения
    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не удалось записать файл');
});

it('exports multiple methods with examples', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_export_test_multiple_');

    // Создаем OpenRPC с несколькими методами
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.3.2');

    // Первый метод
    $method1 = new Method();
    $method1->setName('test.method1');
    $method1->setSummary('First test method');

    $example1 = new ExamplePairingObject();
    $example1->setName('Example 1');

    $result1 = new ExampleObject();
    $result1->setName('result');
    $result1->setValue([
        'data' => 'method1',
    ]);
    $example1->setResult($result1);

    $method1->setExamples([$example1]);

    // Второй метод
    $method2 = new Method();
    $method2->setName('test.method2');
    $method2->setSummary('Second test method');

    $example2 = new ExamplePairingObject();
    $example2->setName('Example 2');

    $result2 = new ExampleObject();
    $result2->setName('result');
    $result2->setValue([
        'data' => 'method2',
    ]);
    $example2->setResult($result2);

    $method2->setExamples([$example2]);

    $openRPC->setMethods([$method1, $method2]);

    // Настраиваем мок
    $this->mockService->expects('build')
        ->once()
        ->andReturns($openRPC);

    // Выполняем команду
    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    // Проверяем результат выполнения
    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);

    // Проверяем содержимое файла
    $exportedData = json_decode(file_get_contents($tempFile), true);
    expect($exportedData['methods'])->toHaveCount(2)
        ->and($exportedData['methods'][0]['name'])->toBe('test.method1')
        ->and($exportedData['methods'][1]['name'])->toBe('test.method2');

    // Удаляем временный файл
    unlink($tempFile);
});
