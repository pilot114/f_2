<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Security;

use App\Common\Attribute\AbstractAccessRightAttribute;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\Security\Attribute\CpActionLoader;
use App\System\Security\Attribute\CpMenuLoader;
use App\System\Security\AuthUserChecker;
use Mockery;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

beforeEach(function (): void {
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->rpcLoader = Mockery::mock(RpcMethodLoader::class);
    $this->cpActionLoader = Mockery::mock(CpActionLoader::class);
    $this->cpMenuLoader = Mockery::mock(CpMenuLoader::class);
    $this->security = Mockery::mock(Security::class);
});

afterEach(function (): void {
    Mockery::close();
});

it('skips cp action check when skipAuth is true', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        true, // skipAuth = true
        $this->security
    );

    // Should not call any methods
    $this->rpcLoader->shouldNotReceive('getFqnByMethodName');
    $this->cpActionLoader->shouldNotReceive('loadByFqn');

    $checker->checkCpActions('someMethod');

    expect(true)->toBeTrue(); // No exception thrown
});

it('skips cp menu check when skipAuth is true', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        true, // skipAuth = true
        $this->security
    );

    $this->rpcLoader->shouldNotReceive('getFqnByMethodName');
    $this->cpMenuLoader->shouldNotReceive('loadByFqn');

    $checker->checkCpMenu('someMethod');

    expect(true)->toBeTrue();
});

it('returns early when fqn is null for cp actions', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn(null);

    $this->cpActionLoader->shouldNotReceive('loadByFqn');

    $checker->checkCpActions('someMethod');

    expect(true)->toBeTrue();
});

it('returns early when fqn is null for cp menu', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn(null);

    $this->cpMenuLoader->shouldNotReceive('loadByFqn');

    $checker->checkCpMenu('someMethod');

    expect(true)->toBeTrue();
});

it('returns early when attribute is not found for cp actions', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn('App\\Some\\Class');

    $this->cpActionLoader->shouldReceive('loadByFqn')
        ->with('App\\Some\\Class')
        ->andReturn(null);

    $checker->checkCpActions('someMethod');

    expect(true)->toBeTrue();
});

it('checks cp actions successfully when user has rights', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn('App\\Some\\Class');

    $attribute = Mockery::mock(AbstractAccessRightAttribute::class);
    $attribute->expression = 'some.permission';
    $attribute->shouldReceive('setContext')
        ->with($currentUser, $this->secRepo)
        ->once();
    $attribute->shouldReceive('check')->andReturn(true);

    $this->cpActionLoader->shouldReceive('loadByFqn')
        ->with('App\\Some\\Class')
        ->andReturn($attribute);

    $checker->checkCpActions('someMethod');

    expect(true)->toBeTrue();
});

it('throws AccessDeniedHttpException when user has no cp action rights', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn('App\\Some\\Class');

    $attribute = Mockery::mock(AbstractAccessRightAttribute::class);
    $attribute->expression = 'admin.users';
    $attribute->shouldReceive('setContext')
        ->with($currentUser, $this->secRepo)
        ->once();
    $attribute->shouldReceive('check')->andReturn(false);

    $this->cpActionLoader->shouldReceive('loadByFqn')
        ->with('App\\Some\\Class')
        ->andReturn($attribute);

    $checker->checkCpActions('someMethod');
})->throws(AccessDeniedHttpException::class, 'Нет прав на cp_action: admin.users');

it('checks cp menu successfully when user has rights', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn('App\\Some\\Class');

    $attribute = Mockery::mock(AbstractAccessRightAttribute::class);
    $attribute->expression = 'menu.item';
    $attribute->shouldReceive('setContext')
        ->with($currentUser, $this->secRepo)
        ->once();
    $attribute->shouldReceive('check')->andReturn(true);

    $this->cpMenuLoader->shouldReceive('loadByFqn')
        ->with('App\\Some\\Class')
        ->andReturn($attribute);

    $checker->checkCpMenu('someMethod');

    expect(true)->toBeTrue();
});

it('throws AccessDeniedHttpException when user has no cp menu rights', function (): void {
    $checker = new AuthUserChecker(
        $this->secRepo,
        $this->rpcLoader,
        $this->cpActionLoader,
        $this->cpMenuLoader,
        false,
        $this->security
    );

    $currentUser = createSecurityUser();
    $this->security->shouldReceive('getUser')->andReturn($currentUser);

    $this->rpcLoader->shouldReceive('getFqnByMethodName')
        ->with('someMethod')
        ->andReturn('App\\Some\\Class');

    $attribute = Mockery::mock(AbstractAccessRightAttribute::class);
    $attribute->expression = 'restricted.menu';
    $attribute->shouldReceive('setContext')
        ->with($currentUser, $this->secRepo)
        ->once();
    $attribute->shouldReceive('check')->andReturn(false);

    $this->cpMenuLoader->shouldReceive('loadByFqn')
        ->with('App\\Some\\Class')
        ->andReturn($attribute);

    $checker->checkCpMenu('someMethod');
})->throws(AccessDeniedHttpException::class, 'Нет прав на cp_menu: restricted.menu');
