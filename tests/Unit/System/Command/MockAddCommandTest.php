<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command\MockManagement;

use App\System\Command\MockManagement\MockAddCommand;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenAPI\Schemas;
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

    $this->command = new MockAddCommand($loader);
    $this->commandTester = new CommandTester($this->command);

    // Создаем мок для OpenRpcMockSpecBuilder
    /** @var MockInterface|OpenRpcMockSpecBuilder $mockService */
    $this->mockService = Mockery::mock(OpenRpcMockSpecBuilder::class);
    $this->mockService->allows('setMockRepository');

    // Теперь можно легко заменить свойство через рефлексию
    $reflection = new ReflectionClass($this->command);
    $property = $reflection->getProperty('mockService');
    $property->setAccessible(true);
    $property->setValue($this->command, $this->mockService);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock:add')
        ->and($this->command->getDescription())->toBe('Добавить мок-ответ для API метода');
});

it('adds default mock without params', function (): void {
    $methodName = 'test.method';
    $response = [
        'result' => 'success',
    ];
    $responseJson = json_encode($response);

    $this->mockService->expects('addMock')
        ->with($methodName, $response, null)
        ->andReturns(true);

    $this->commandTester->execute([
        'method'   => $methodName,
        'response' => $responseJson,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Дефолтный мок для метода test.method добавлен');
});

it('adds mock with params', function (): void {
    $methodName = 'test.method';
    $response = [
        'result' => 'success',
    ];
    $responseJson = json_encode($response);
    $params = [
        'param1' => 'value1',
        'param2' => 123,
    ];
    $paramsJson = json_encode($params);

    $this->mockService->expects('addMock')
        ->with($methodName, $response, $params)
        ->andReturns(true);

    $this->commandTester->execute([
        'method'   => $methodName,
        'response' => $responseJson,
        'params'   => $paramsJson,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Мок для метода test.method с параметрами добавлен');
});

it('fails when method name is not a string', function (): void {
    $this->commandTester->execute([
        'method'   => null,
        'response' => '{"result":"success"}',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Имя метода должно быть строкой');
});

it('fails when response is not a string', function (): void {
    $this->commandTester->execute([
        'method'   => 'test.method',
        'response' => null,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Ответ должен быть строкой JSON');
});

it('fails when response JSON is invalid', function (): void {
    $this->commandTester->execute([
        'method'   => 'test.method',
        'response' => '{invalid:json}',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Неверный формат JSON для ответа');
});

it('fails when params is not a string', function (): void {
    $this->commandTester->execute([
        'method'   => 'test.method',
        'response' => '{"result":"success"}',
        'params'   => 123,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Параметры должны быть строкой JSON');
});

it('fails when params JSON is invalid', function (): void {
    $this->commandTester->execute([
        'method'   => 'test.method',
        'response' => '{"result":"success"}',
        'params'   => '{invalid:json}',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Неверный формат JSON для параметров');
});

it('fails when params is not an array', function (): void {
    $this->commandTester->execute([
        'method'   => 'test.method',
        'response' => '{"result":"success"}',
        'params'   => '"not_an_array"',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Параметры должны быть массивом');
});

it('fails when service throws exception', function (): void {
    $methodName = 'test.method';
    $response = [
        'result' => 'success',
    ];
    $responseJson = json_encode($response);
    $errorMessage = 'Тестовая ошибка';

    $this->mockService->expects('addMock')
        ->with($methodName, $response, null)
        ->andThrow(new Exception($errorMessage));

    $this->commandTester->execute([
        'method'   => $methodName,
        'response' => $responseJson,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не удалось cохранить мок ' . $errorMessage);
});
