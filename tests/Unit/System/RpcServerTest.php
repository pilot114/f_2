<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\Common\Attribute\RpcMethod;
use App\System\CustomHttpKernel;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\DTO\RpcArgumentResolver;
use App\System\RPC\RpcServer;
use App\System\Security\AuthUserChecker;
use Generator;
use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class SumController
{
    public function __invoke(int ...$args): int
    {
        return array_sum($args);
    }
}

it('handle rpc requests', function (string $request, array $response): void {
    $kernel = Mockery::mock(CustomHttpKernel::class);
    $dispatcher = Mockery::mock(EventDispatcher::class);
    $loader = Mockery::mock(RpcMethodLoader::class);

    $userChecker = Mockery::mock(AuthUserChecker::class);
    $userChecker->shouldReceive('checkCpActions');
    $userChecker->shouldReceive('checkCpMenu');
    $loader->shouldReceive('load')
        ->once()
        ->andReturnUsing(function (): Generator {
            $fqn = SumController::class . '::' . '__invoke';
            $method = new RpcMethod('example.test.sum', 'расчёт суммы');
            $method->fqn = $fqn;
            yield $fqn => $method;
        });
    $dispatcher->shouldReceive('dispatch');

    $server = new RpcServer(new RpcArgumentResolver(), new ControllerResolver(), $loader, $userChecker, env: 'dev');
    $server->setContext($kernel, $dispatcher, 1);

    $server->load();

    $request = Request::create(uri: RpcServer::RPC_URL, content: $request);

    ##########################################
    $result = $server->handleRequest($request);
    ##########################################

    expect($response)->toEqual($result);
})->with('rpcRequests');
