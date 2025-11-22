<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\System\Command\DbUsageReport;
use App\System\DomainSourceCodeFinder;
use Database\Schema\EntityRetriever;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    DbUsageReport::$likeTables = [];
    DbUsageReport::$likeProcedures = [];
    $this->fileLoader = Mockery::mock(DomainSourceCodeFinder::class);
    $this->retriever = Mockery::mock(EntityRetriever::class);
    $this->command = new DbUsageReport($this->fileLoader, $this->retriever);
    $this->commandTester = new CommandTester($this->command);
});

afterEach(function (): void {
    Mockery::close();
});

it('проверяет корректную настройку команды', function (): void {
    $definition = $this->command->getDefinition();

    expect($definition->hasArgument('subdomain'))->toBeTrue()
        ->and($definition->getArgument('subdomain')->isRequired())->toBeTrue()
        ->and($definition->getArgument('subdomain')->getDescription())->toBe('поддомен (domain.subdomain)');
});

it('имеет статические свойства для хранения таблиц и процедур', function (): void {
    $reflection = new ReflectionClass(DbUsageReport::class);

    expect($reflection->hasProperty('likeTables'))->toBeTrue()
        ->and($reflection->hasProperty('likeProcedures'))->toBeTrue();

    $tablesProperty = $reflection->getProperty('likeTables');
    $proceduresProperty = $reflection->getProperty('likeProcedures');

    expect($tablesProperty->isStatic())->toBeTrue()
        ->and($proceduresProperty->isStatic())->toBeTrue();
});

it('имеет корректное название и описание', function (): void {
    expect($this->command->getName())->toBe('system:dbUsageReport')
        ->and($this->command->getDescription())->toBe('отчёт по использованию БД в поддомене');
});
