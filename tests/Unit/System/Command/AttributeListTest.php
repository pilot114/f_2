<?php

declare(strict_types=1);

use App\Common\Attribute\CpAction;
use App\Common\Attribute\CpMenu;
use App\System\Command\AttributeList;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\Security\Attribute\CpActionLoader;
use App\System\Security\Attribute\CpMenuLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->rpcLoader = mock(RpcMethodLoader::class);
    $this->cpActionLoader = mock(CpActionLoader::class);
    $this->cpMenuLoader = mock(CpMenuLoader::class);

    $this->command = new AttributeList(
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader
    );
});

it('has correct name and description', function (): void {
    expect($this->command->getName())->toBe('system:attributeList');
    expect($this->command->getDescription())->toBe('список зарегистрированных аттрибутов');
});

it('executes successfully and displays table with rpc methods', function (): void {
    $rpcMethod = (object) [
        'name' => 'test.method',
    ];
    $cpAction = mock(CpAction::class);
    $cpAction->expression = 'admin';
    $cpMenu = mock(CpMenu::class);
    $cpMenu->expression = 'menu.test';

    $this->rpcLoader
        ->shouldReceive('load')
        ->once()
        ->andReturn((function () use ($rpcMethod) {
            yield 'App\\Controller\\TestController::method' => $rpcMethod;
        })());

    $this->cpActionLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn($cpAction);

    $this->cpMenuLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn($cpMenu);

    $input = new StringInput('');
    $output = new BufferedOutput();

    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $fetch = $output->fetch();
    expect($fetch)->toContain('test.method');
    expect($fetch)->toContain('admin');
    expect($fetch)->toContain('menu.test');
});

it('handles methods without cp action attribute', function (): void {
    $rpcMethod = (object) [
        'name' => 'test.method',
    ];

    $this->rpcLoader
        ->shouldReceive('load')
        ->once()
        ->andReturn((function () use ($rpcMethod) {
            yield 'App\\Controller\\TestController::method' => $rpcMethod;
        })());

    $this->cpActionLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn(null);

    $this->cpMenuLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn(null);

    $input = new StringInput('');
    $output = new BufferedOutput();

    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $fetch = $output->fetch();
    expect($fetch)->toContain('test.method');
});

it('handles methods with only cp action attribute', function (): void {
    $rpcMethod = (object) [
        'name' => 'test.method',
    ];
    $cpAction = mock(CpAction::class);
    $cpAction->expression = 'user';

    $this->rpcLoader
        ->shouldReceive('load')
        ->once()
        ->andReturn((function () use ($rpcMethod) {
            yield 'App\\Controller\\TestController::method' => $rpcMethod;
        })());

    $this->cpActionLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn($cpAction);

    $this->cpMenuLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn(null);

    $input = new StringInput('');
    $output = new BufferedOutput();

    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $fetch = $output->fetch();
    expect($fetch)->toContain('test.method');
    expect($fetch)->toContain('user');
});

it('handles methods with only cp menu attribute', function (): void {
    $rpcMethod = (object) [
        'name' => 'test.method',
    ];
    $cpMenu = mock(CpMenu::class);
    $cpMenu->expression = 'menu.test';

    $this->rpcLoader
        ->shouldReceive('load')
        ->once()
        ->andReturn((function () use ($rpcMethod) {
            yield 'App\\Controller\\TestController::method' => $rpcMethod;
        })());

    $this->cpActionLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn(null);

    $this->cpMenuLoader
        ->shouldReceive('loadByFqn')
        ->with('App\\Controller\\TestController::method')
        ->once()
        ->andReturn($cpMenu);

    $input = new StringInput('');
    $output = new BufferedOutput();

    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $fetch = $output->fetch();
    expect($fetch)->toContain('test.method');
    expect($fetch)->toContain('menu.test');
});

it('handles empty rpc methods list', function (): void {
    $this->rpcLoader
        ->shouldReceive('load')
        ->once()
        ->andReturn((function () {
            if (false) {
                yield;
            }
        })());

    $input = new StringInput('');
    $output = new BufferedOutput();

    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $fetch = $output->fetch();
    expect($fetch)->toContain('RPC, CpAction, CpMenu');
});
