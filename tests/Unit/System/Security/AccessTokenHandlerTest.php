<?php

declare(strict_types=1);

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\System\Security\AccessTokenHandler;
use App\System\Security\JWT;
use Database\Connection\CpConnection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

beforeEach(function (): void {
    $this->jwt = mock(JWT::class);
    $this->connection = mock(CpConnection::class);
    $this->container = mock(Container::class);

    $this->handler = new AccessTokenHandler(
        $this->jwt,
        $this->connection,
        $this->container
    );
});

it('implements access token handler interface', function (): void {
    expect($this->handler)->toBeInstanceOf(AccessTokenHandlerInterface::class);
});

it('creates user badge from valid token', function (): void {
    $tokenData = [
        'id'    => 123,
        'name'  => 'John Doe',
        'email' => 'john@example.com',
        'login' => 'johndoe',
    ];

    $this->jwt
        ->shouldReceive('decode')
        ->with('valid-token')
        ->once()
        ->andReturn($tokenData);

    $this->container
        ->shouldReceive('set')
        ->with(SecurityUser::class, Mockery::on(function (SecurityUser $user) use ($tokenData): bool {
            return $user->id === $tokenData['id']
                && $user->name === $tokenData['name']
                && $user->email === $tokenData['email'];
        }))
        ->once();

    $this->connection
        ->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', [
            'login' => 'johndoe',
        ])
        ->once();

    $badge = $this->handler->getUserBadgeFrom('valid-token');

    expect($badge)->toBeInstanceOf(UserBadge::class);
    expect($badge->getUserIdentifier())->toBe('john@example.com');
});

it('handles token data without login field', function (): void {
    $tokenData = [
        'id'    => 456,
        'name'  => 'Jane Doe',
        'email' => 'jane@example.com',
    ];

    $this->jwt
        ->shouldReceive('decode')
        ->with('token-without-login')
        ->once()
        ->andReturn($tokenData);

    $this->container
        ->shouldReceive('set')
        ->with(SecurityUser::class, Mockery::any())
        ->once();

    $this->connection
        ->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', [
            'login' => '',
        ])
        ->once();

    $badge = $this->handler->getUserBadgeFrom('token-without-login');

    expect($badge)->toBeInstanceOf(UserBadge::class);
    expect($badge->getUserIdentifier())->toBe('jane@example.com');
});

it('handles token data without email field', function (): void {
    $tokenData = [
        'id'    => 789,
        'name'  => 'No Email User',
        'login' => 'nomail',
    ];

    $this->jwt
        ->shouldReceive('decode')
        ->with('token-without-email')
        ->once()
        ->andReturn($tokenData);

    $this->container
        ->shouldReceive('set')
        ->with(SecurityUser::class, Mockery::any())
        ->once();

    $this->connection
        ->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', [
            'login' => 'nomail',
        ])
        ->once();

    $badge = $this->handler->getUserBadgeFrom('token-without-email');

    expect($badge)->toBeInstanceOf(UserBadge::class);
    expect($badge->getUserIdentifier())->toBe('');
});

it('throws http exception for invalid token', function (): void {
    $this->jwt
        ->shouldReceive('decode')
        ->with('invalid-token')
        ->once()
        ->andReturn(false);

    $this->container
        ->shouldNotReceive('set');

    $this->connection
        ->shouldNotReceive('procedure');

    expect(fn () => $this->handler->getUserBadgeFrom('invalid-token'))
        ->toThrow(HttpException::class, 'invalid token');
});
