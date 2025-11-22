<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\System\Command\Shared;
use App\System\Command\SqlFormatter;
use App\System\DomainSourceCodeFinder;
use Doctrine\SqlFormatter\SqlFormatter as Formatter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->fileLoader = Mockery::mock(DomainSourceCodeFinder::class);
    $this->formatter = new Formatter();
    $this->command = new SqlFormatter($this->formatter, $this->fileLoader);
});

afterEach(function (): void {
    Mockery::close();
});

it('инициализирует shared объект при создании команды', function (): void {
    expect(SqlFormatter::$shared)->toBeInstanceOf(Shared::class);
});

it('проверяет корректную настройку команды', function (): void {
    $definition = $this->command->getDefinition();

    expect($definition->hasArgument('sql'))->toBeTrue()
        ->and($definition->getArgument('sql')->isRequired())->toBeFalse()
        ->and($definition->getArgument('sql')->getDescription())->toBe('sql запрос');
});

it('имеет корректное имя и описание команды', function (): void {
    expect($this->command->getName())->toBe('system:sqlFormatter')
        ->and($this->command->getDescription())->toBe('форматирование SQL запросов');
});

it('имеет зависимость от Formatter и DomainSourceCodeFinder', function (): void {
    $reflection = new ReflectionClass(SqlFormatter::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('formatter')
        ->and($parameters[1]->getName())->toBe('fileLoader');
});
