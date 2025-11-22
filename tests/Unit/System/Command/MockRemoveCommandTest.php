<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command\MockManagement;

use App\System\Command\MockManagement\MockRemoveCommand;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
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
    $loader->allows('load')->andReturns((function () {
        if (false) {
            yield;
        }
    })());

    $this->command = new MockRemoveCommand($loader);
    $this->commandTester = new CommandTester($this->command);

    // 1. Создаем РЕАЛЬНЫЙ, полностью сконструированный объект билдера
    $realSpecBuilder = new OpenRpcMockSpecBuilder(
        (function () { if (false) { yield; } })(),
        new Schemas(),
        'test'
    );

    // 2. Создаем мок репозитория и устанавливаем его, так как он нужен билдеру
    /** @var MockInterface|MockSpecRepository $repository */
    $repository = Mockery::mock(MockSpecRepository::class);
    $realSpecBuilder->setMockRepository($repository);

    // 3. Теперь создаем частичный мок из этого ГОТОВОГО объекта
    $this->mockService = Mockery::mock($realSpecBuilder)->makePartial();

    // 4. Внедряем наш корректный, "гибридный" мок в команду
    $reflection = new ReflectionClass($this->command);
    $property = $reflection->getProperty('mockService');
    $property->setAccessible(true);
    $property->setValue($this->command, $this->mockService);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock:remove')
        ->and($this->command->getDescription())->toBe('Удалить мок-ответ для API метода');
});

it('removes all mocks for a method when no params are given', function (): void {
    $methodName = 'test.method.all';

    $this->mockService->expects('removeMock')
        ->with($methodName, null)
        ->once()
        ->andReturns(true);

    $this->commandTester->execute([
        'method' => $methodName,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Все моки для метода ' . $methodName . ' удалены');
});

it('removes a specific mock when params are given', function (): void {
    $methodName = 'test.method.specific';
    $params = [
        'id'     => 123,
        'filter' => 'active',
    ];
    $paramsJson = json_encode($params);

    $this->mockService->expects('removeMock')
        ->with($methodName, $params)
        ->once()
        ->andReturns(true);

    $this->commandTester->execute([
        'method' => $methodName,
        'params' => $paramsJson,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Мок для метода ' . $methodName . ' с параметрами удален');
});

it('fails gracefully when the service throws an exception', function (): void {
    $methodName = 'test.method.fail';
    $errorMessage = 'Mock service is unavailable';

    $this->mockService->expects('removeMock')
        ->with($methodName, null)
        ->once()
        ->andThrow(new Exception($errorMessage));

    $this->commandTester->execute([
        'method' => $methodName,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не удалось удалить мок: ' . $errorMessage);
});

it('fails when method name is not a string', function (): void {
    $this->commandTester->execute([
        'method' => null,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Имя метода должно быть строкой');
});

it('fails when params JSON is invalid', function (): void {
    $this->commandTester->execute([
        'method' => 'test.method',
        'params' => '{invalid-json',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Неверный формат JSON для параметров');
});

it('fails when params is not a JSON array', function (): void {
    $this->commandTester->execute([
        'method' => 'test.method',
        'params' => '"just-a-string"',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Параметры должны быть массивом');
});
