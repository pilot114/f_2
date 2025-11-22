<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\System\Command\MockManagement;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->command = new MockManagement();
    $this->commandTester = new CommandTester($this->command);
});

it('configures command correctly', function (): void {
    expect($this->command->getName())->toBe('system:mock')
        ->and($this->command->getDescription())->toBe('Управление мок-ответами для API методов');
});

it('displays list of subcommands and examples', function (): void {
    $this->commandTester->execute([]);

    $output = $this->commandTester->getDisplay();

    // Проверяем наличие заголовка
    expect($output)->toContain('Управление мок-ответами для API методов')
        ->and($output)->toContain('system:mock:add')
        ->and($output)->toContain('system:mock:remove')
        ->and($output)->toContain('system:mock:list')
        ->and($output)->toContain('system:mock:import')
        ->and($output)->toContain('system:mock:export')
        ->and($output)->toContain('Примеры использования:')
        ->and($output)->toContain('system:mock:add method_name')
        ->and($output)->toContain('system:mock:remove method_name')
        ->and($output)->toContain('system:mock:list')
        ->and($output)->toContain('system:mock:import /path/to/file.json')
        ->and($output)->toContain('system:mock:export /path/to/file.json')
        ->and($this->commandTester->getStatusCode())->toBe(Command::SUCCESS);

});
