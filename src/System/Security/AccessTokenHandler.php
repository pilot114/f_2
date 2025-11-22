<?php

declare(strict_types=1);

namespace App\System\Security;

use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\CpConnection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private JWT $jwt,
        private CpConnection $conn,
        // нужно для оптимизации
        private Container $container,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $data = $this->jwt->decode($accessToken);

        if ($data === false) {
            throw HttpException::fromStatusCode(Response::HTTP_UNAUTHORIZED, 'invalid token');
        }

        // сохраняем в контейнер, чтобы в UserProvider уже не вытягивать из БД
        // TODO: если меняется name/email, токен нужно зарефрешить
        $this->container->set(SecurityUser::class, new SecurityUser(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            login: $data['login'] ?? '',
        ));

        $this->conn->procedure('acl.pacl.authorize_from_cp', [
            'login' => $data['login'] ?? '',
        ]);

        return new UserBadge($data['email'] ?? '');
    }
}
