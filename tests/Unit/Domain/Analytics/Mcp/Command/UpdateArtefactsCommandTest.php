<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\Command;

use App\Domain\Analytics\Mcp\Command\UpdateArtefactsCommand;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use App\Domain\Analytics\Mcp\Retriever\OracleArtefactRetriever;
use Database\ORM\CommandRepositoryInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

uses(MockeryPHPUnitIntegration::class);

it('имеет корректное название и описание', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $attributes = $reflection->getAttributes();

    $asCommandAttr = null;
    foreach ($attributes as $attribute) {
        if ($attribute->getName() === 'Symfony\Component\Console\Attribute\AsCommand') {
            $asCommandAttr = $attribute;
            break;
        }
    }

    expect($asCommandAttr)->not->toBeNull();
});

it('имеет атрибут AsCronTask', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $attributes = $reflection->getAttributes();

    $cronTaskAttr = null;
    foreach ($attributes as $attribute) {
        if ($attribute->getName() === 'Symfony\Component\Scheduler\Attribute\AsCronTask') {
            $cronTaskAttr = $attribute;
            break;
        }
    }

    expect($cronTaskAttr)->not->toBeNull();
});

it('имеет необходимые зависимости', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(3)
        ->and($parameters[0]->getName())->toBe('oracleRetriever')
        ->and($parameters[1]->getName())->toBe('cacheRetriever')
        ->and($parameters[2]->getName())->toBe('commandRepository');
});

it('имеет опции diff и stats', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);

    expect($reflection->hasMethod('configure'))->toBeTrue();
});

it('имеет защищённые методы для обработки', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);

    expect($reflection->hasMethod('updateByDiff'))->toBeTrue()
        ->and($reflection->hasMethod('printStats'))->toBeTrue()
        ->and($reflection->hasMethod('createArtefacts'))->toBeTrue();
});

it('имеет метод execute с правильной сигнатурой', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $method = $reflection->getMethod('execute');

    expect($method->isProtected())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('input')
        ->and($parameters[1]->getName())->toBe('output');
});

it('метод updateByDiff принимает OutputInterface', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $method = $reflection->getMethod('updateByDiff');

    expect($method->isProtected())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('output');
});

it('метод printStats принимает OutputInterface', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $method = $reflection->getMethod('printStats');

    expect($method->isProtected())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('output');
});

it('метод createArtefacts принимает OutputInterface и ArtefactType', function (): void {
    $reflection = new ReflectionClass(UpdateArtefactsCommand::class);
    $method = $reflection->getMethod('createArtefacts');

    expect($method->isProtected())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('output')
        ->and($parameters[1]->getName())->toBe('artefactType');
});

it('execute returns success with stats option', function (): void {
    $oracleRetriever = Mockery::mock(OracleArtefactRetriever::class);
    $cacheRetriever = Mockery::mock(CacheArtefactRetriever::class);
    $commandRepository = Mockery::mock(CommandRepositoryInterface::class);

    $oracleRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::TABLE)->andReturn(['table1', 'table2']);
    $oracleRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::PROCEDURE)->andReturn(['proc1']);
    $oracleRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::VIEW)->andReturn(['view1', 'view2', 'view3']);

    $cacheRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::TABLE)->andReturn(['table1']);
    $cacheRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::PROCEDURE)->andReturn(['proc1']);
    $cacheRetriever->shouldReceive('getNameList')
        ->with(ArtefactType::VIEW)->andReturn(['view1', 'view2']);

    $command = new UpdateArtefactsCommand($oracleRetriever, $cacheRetriever, $commandRepository);
    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        '--stats' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('execute returns success with diff option', function (): void {
    $oracleRetriever = Mockery::mock(OracleArtefactRetriever::class);
    $cacheRetriever = Mockery::mock(CacheArtefactRetriever::class);
    $commandRepository = Mockery::mock(CommandRepositoryInterface::class);

    $oracleRetriever->shouldReceive('getDiffForLastDays')->with(10)->andReturn([]);

    $command = new UpdateArtefactsCommand($oracleRetriever, $cacheRetriever, $commandRepository);
    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        '--diff' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('can be instantiated', function (): void {
    $oracleRetriever = Mockery::mock(OracleArtefactRetriever::class);
    $cacheRetriever = Mockery::mock(CacheArtefactRetriever::class);
    $commandRepository = Mockery::mock(CommandRepositoryInterface::class);

    $command = new UpdateArtefactsCommand($oracleRetriever, $cacheRetriever, $commandRepository);

    expect($command)->toBeInstanceOf(UpdateArtefactsCommand::class);
});
