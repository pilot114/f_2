<?php

declare(strict_types=1);

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Kernel;
use Database\Connection\CpConfig;
use Database\Connection\CpConnection;
use Database\Factory;
use Database\ORM\DataMapper;
use Database\ORM\DataMapperInterface;
use Symfony\Component\HttpFoundation\Request;

function testRpcCall(string $method, array $params): array
{
    $_ENV['DB_IS_PROD'] = 'false';
    $_ENV['SKIP_AUTH'] = 'true';
    $kernel = new Kernel('test', true);
    $request = new Request(
        server: [
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/api/v2/rpc',
            'HTTP_AUTHORIZATION' => $_ENV['JWT_DEBUG'],
        ],
        content: sprintf(
            '{"jsonrpc": "2.0", "method": "%s", "params": %s, "id": "%s"}',
            $method,
            json_encode($params),
            uniqid()
        )
    );

    $response = $kernel->handle($request);
    $result = json_decode($response->getContent(), true);

    if (isset($result['error'])) {
        throw new Exception(json_encode($result['error'], JSON_UNESCAPED_UNICODE));
    }

    return $result;
}

function getTestConnection(bool $isEcho = true): CpConnection
{
    return Factory::buildCpConnection(isProd: false, isEcho: $isEcho);
}

function getDataMapper(): DataMapperInterface
{
    return new DataMapper();
}

/**
 * Получить ID из указанной таблицы
 */
function getMaxIdFromTable(string $tableName, array $where = []): int
{
    $lines = explode("\n", file_get_contents(__DIR__ . '/../.env.local'));
    $dbPass = array_filter($lines, fn (string $x): bool => str_starts_with($x, 'DB_PASS'));
    $dbPass = explode('=', array_values($dbPass)[0])[1];

    $conn = new CpConnection(new CpConfig(
        user: 'tech_corp_portal[test]',
        password: $dbPass,
        isProd: false,
    ));
    return (int) $conn->max($tableName, 'id', $where);
}

/**
 * Создать тестовый SecurityUser с дефолтными значениями
 */
function createSecurityUser(
    int $id = 4026,
    string $name = 'Test User',
    string $email = 'test@example.com',
    string $login = 'test_login',
    array $roles = [],
    array $permissions = []
): SecurityUser {
    return new SecurityUser(
        id: $id,
        name: $name,
        email: $email,
        login: $login,
        roles: $roles,
        permissions: $permissions
    );
}
