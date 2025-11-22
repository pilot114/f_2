<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\System\Command\Generate;
use App\System\DomainSourceCodeFinder;
use App\System\RPC\Attribute\RpcMethodLoader;
use Database\Schema\EntityRetriever;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->retriever = Mockery::mock(EntityRetriever::class);
    $this->rpcLoader = Mockery::mock(RpcMethodLoader::class);
    $this->finder = Mockery::mock(DomainSourceCodeFinder::class);
    $this->projectDir = '/tmp/test_project';

    $emptyGenerator = (function (): Generator {
        if (false) {
            yield;
        }
    })();

    $this->rpcLoader->allows('load')->andReturn($emptyGenerator);
    $this->finder->allows('getDomainDirs')->andReturns([
        ['Finance', 'Kpi'],
        ['Hr', 'Achievements'],
    ]);

    $this->command = new Generate(
        $this->retriever,
        $this->rpcLoader,
        $this->finder,
        $this->projectDir
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('инициализирует команду с доменами', function (): void {
    expect($this->command)->toBeInstanceOf(Generate::class);

    $reflection = new ReflectionClass($this->command);
    $domainsProperty = $reflection->getProperty('domains');
    $domains = $domainsProperty->getValue($this->command);

    expect($domains)->toBeArray()
        ->toHaveKey('finance')
        ->toHaveKey('hr');
});

it('проверяет название команды и описание', function (): void {
    expect($this->command->getName())->toBe('system:make')
        ->and($this->command->getDescription())->toBe('Генерация кода в интерактивном режиме');
});

it('имеет необходимые зависимости', function (): void {
    $reflection = new ReflectionClass(Generate::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(4)
        ->and($parameters[0]->getName())->toBe('retriever')
        ->and($parameters[1]->getName())->toBe('rpcLoader')
        ->and($parameters[2]->getName())->toBe('finder')
        ->and($parameters[3]->getName())->toBe('projectDir');
});
