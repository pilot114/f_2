<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command\MockManagement;

use App\System\Command\MockManagement\MockImportCommand;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenAPI\Schemas;
use PSX\OpenRPC\OpenRPC;
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

    $this->command = new MockImportCommand($loader);
    $this->commandTester = new CommandTester($this->command);

    // Создаем РЕАЛЬНЫЙ, полностью сконструированный объект
    $realSpecBuilder = new OpenRpcMockSpecBuilder(
        (function () { if (false) { yield; } })(),
        new Schemas(),
        'test'
    );

    // Создаем мок репозитория и устанавливаем его на РЕАЛЬНЫЙ объект
    /** @var MockInterface|MockSpecRepository $repository */
    $repository = Mockery::mock(MockSpecRepository::class);
    $realSpecBuilder->setMockRepository($repository);

    // Теперь создаем частичный мок из полностью готового объекта
    $this->mockService = Mockery::mock($realSpecBuilder)->makePartial();

    // Внедряем наш корректный мок в команду
    $reflection = new ReflectionClass($this->command);
    $property = $reflection->getProperty('mockService');
    $property->setAccessible(true);
    $property->setValue($this->command, $this->mockService);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock:import')
        ->and($this->command->getDescription())->toBe('Импортировать мок-ответы из файла');
});

it('imports mocks from file successfully', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_import_');
    $mockData = [
        'openrpc' => '1.3.2',
        'methods' => [[
            'name' => 'test.method',
        ]],
    ];
    file_put_contents($tempFile, json_encode($mockData));

    // Настраиваем ожидания для методов, которые будут вызваны
    $this->mockService->expects('setMockJsonSpec')->once()->with(json_encode($mockData));
    $this->mockService->expects('build')->once()->andReturn(new OpenRPC());
    $this->mockService->expects('saveMockSpec')->once()->with(Mockery::type(OpenRPC::class))->andReturns(true);

    // Выполняем команду
    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    // Проверяем результат
    expect($this->commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Мок-ответы успешно импортированы');

    unlink($tempFile);
});

it('fails if file not found', function (): void {
    $this->commandTester->execute([
        'file' => '/non/existent/file.json',
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Файл не найден');
});

it('fails on invalid JSON', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_import_');
    file_put_contents($tempFile, '{invalid:json}');

    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не удалось разобрать JSON');

    unlink($tempFile);
});

it('fails when save operation fails', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'mock_import_');
    $mockData = [
        'openrpc' => '1.3.2',
        'methods' => [],
    ];
    file_put_contents($tempFile, json_encode($mockData));

    $this->mockService->expects('setMockJsonSpec')->once();
    $this->mockService->expects('build')->once()->andReturn(new OpenRPC());
    $this->mockService->expects('saveMockSpec')->once()->andReturns(false);

    $this->commandTester->execute([
        'file' => $tempFile,
    ]);

    expect($this->commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не удалось сохранить импортированные мок-ответы');

    unlink($tempFile);
});
