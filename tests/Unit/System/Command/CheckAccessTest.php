<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use App\System\Command\CheckAccess;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->securityRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->command = new CheckAccess($this->securityRepo);
    $this->commandTester = new CommandTester($this->command);
});

afterEach(function (): void {
    Mockery::close();
});

it('успешно проверяет права доступа для пользователей', function (): void {
    // Подготовка тестовых данных
    $code = 'accured_kpi.accured_kpi_superboss';
    $users = '1,2';

    // Создание моков для пользователей
    $user1 = (object) [
        'name' => 'Иван Иванов',
    ];
    $user2 = (object) [
        'name' => 'Петр Петров',
    ];

    // Настраиваем ожидания в порядке, соответствующем реальным вызовам
    // findOneBy для обоих пользователей вызывается до hasCpAction
    $this->securityRepo
        ->expects('findOneBy')
        ->with([
            'id' => '1',
        ])
        ->andReturns($user1);

    $this->securityRepo
        ->expects('findOneBy')
        ->with([
            'id' => '2',
        ])
        ->andReturns($user2);

    // Затем идут вызовы hasCpAction для каждого пользователя
    $this->securityRepo
        ->expects('hasCpAction')
        ->with(1, $code)
        ->andReturns(true);

    $this->securityRepo
        ->expects('hasCpAction')
        ->with(2, $code)
        ->andReturns(false);

    // Выполнение команды
    $exitCode = $this->commandTester->execute([
        'code'  => $code,
        'users' => $users,
    ]);

    // Проверка результата
    expect($exitCode)->toBe(Command::SUCCESS);

    // Проверяем, что вывод содержит ожидаемую информацию
    $display = $this->commandTester->getDisplay();
    expect($display)->toContain('1 Иван Иванов')
        ->toContain('2 Петр Петров');
});

it('возвращает ошибку если пользователь не найден', function (): void {
    // Подготовка тестовых данных
    $code = 'accured_kpi.accured_kpi_superboss';
    $users = '1,999';

    // Создание мока для пользователя
    $user1 = (object) [
        'name' => 'Иван Иванов',
    ];

    // Мокирование репозитория с использованием старого синтаксиса shouldReceive
    $this->securityRepo
        ->expects('findOneBy')
        ->with([
            'id' => '1',
        ])
        ->andReturns($user1);

    $this->securityRepo
        ->expects('findOneBy')
        ->with([
            'id' => '999',
        ])
        ->andReturns(null);

    $this->securityRepo
        ->expects('hasCpAction')
        ->zeroOrMoreTimes();

    // Выполнение команды
    $exitCode = $this->commandTester->execute([
        'code'  => $code,
        'users' => $users,
    ]);

    // Проверка результата
    expect($exitCode)->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Не найден пользователь с id 999');
});

it('проверяет корректную настройку команды', function (): void {
    $definition = $this->command->getDefinition();

    // Проверяем, что у команды есть нужные аргументы
    expect($definition->hasArgument('code'))->toBeTrue()
        ->and($definition->getArgument('code')->isRequired())->toBeTrue()
        ->and($definition->getArgument('code')->getDescription())->toBe('код права доступа, например: accured_kpi.accured_kpi_superboss')
        ->and($definition->hasArgument('users'))->toBeTrue()
        ->and($definition->getArgument('users')->isRequired())->toBeTrue()
        ->and($definition->getArgument('users')->getDescription())->toBe('список id из test.cp_emp через запятую');
});

it('использует hasPermission для числового кода', function (): void {
    $code = '1990'; // числовой код
    $users = '42';

    $user = (object) [
        'name' => 'Test User',
    ];

    $this->securityRepo
        ->expects('findOneBy')
        ->with([
            'id' => '42',
        ])
        ->andReturns($user);

    // Для числового кода должен использоваться hasPermission, а не hasCpAction
    $this->securityRepo
        ->expects('hasPermission')
        ->with(42, 'cp_action', 1990)
        ->andReturns(true);

    $this->securityRepo
        ->expects('hasCpAction')
        ->never();

    $exitCode = $this->commandTester->execute([
        'code'  => $code,
        'users' => $users,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS);
    expect($this->commandTester->getDisplay())->toContain('42 Test User');
});

it('обрабатывает множество пользователей с числовым кодом', function (): void {
    $code = '100';
    $users = '1,2,3';

    $user1 = (object) [
        'name' => 'User 1',
    ];
    $user2 = (object) [
        'name' => 'User 2',
    ];
    $user3 = (object) [
        'name' => 'User 3',
    ];

    $this->securityRepo->expects('findOneBy')->with([
        'id' => '1',
    ])->andReturns($user1);
    $this->securityRepo->expects('findOneBy')->with([
        'id' => '2',
    ])->andReturns($user2);
    $this->securityRepo->expects('findOneBy')->with([
        'id' => '3',
    ])->andReturns($user3);

    $this->securityRepo->expects('hasPermission')->with(1, 'cp_action', 100)->andReturns(true);
    $this->securityRepo->expects('hasPermission')->with(2, 'cp_action', 100)->andReturns(false);
    $this->securityRepo->expects('hasPermission')->with(3, 'cp_action', 100)->andReturns(true);

    $exitCode = $this->commandTester->execute([
        'code'  => $code,
        'users' => $users,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS);
    $display = $this->commandTester->getDisplay();
    expect($display)->toContain('User 1')
        ->and($display)->toContain('User 2')
        ->and($display)->toContain('User 3');
});
