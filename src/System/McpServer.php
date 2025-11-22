<?php

declare(strict_types=1);

namespace App\System;

use PhpMcp\Schema\JsonRpc\Message;
use PhpMcp\Schema\JsonRpc\Notification;
use PhpMcp\Schema\JsonRpc\Request as JsonRpcRequest;
use PhpMcp\Server\Protocol;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;
use Psr\Log\LoggerInterface;
use React\Http\Message\Response as HttpResponse;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class McpServer
{
    public const MCP_URL = '/api/v2/mcp';

    private Server $server;
    private Psr16Cache $cache;
    private object $transport;

    public function __construct(
        private readonly LoggerInterface $logger,
        private Container $container,
        private readonly string $projectDir,
        private string $env,
        string $tmpDir,
    ) {
        $psr6Cache = new FilesystemAdapter(directory: $tmpDir);
        $this->cache = new Psr16Cache($psr6Cache);
    }

    /**
     * Динамическая регистрация MCP эндпоинтов
     */
    public function register(): void
    {
        if ($this->env !== 'dev') {
            return;
        }
        $this->server = Server::make()
            ->withServerInfo('CorPortal MCP Server', '1.0.0')
            ->withLogger($this->logger)
            ->withCache($this->cache)
            ->withContainer($this->container)
            ->withSession('cache', 7200)
            ->build();

        $this->server->discover($this->projectDir, ['src']);
    }

    /**
     * Обработка MCP запроса
     */
    public function handle(Request $request): Response
    {
        // пока использование mcp разрешено только локально (нет авторизации)
        if ($this->env !== 'dev') {
            return new Response('{"result": "Использование mcp разрешено только локально"}', Response::HTTP_UNAUTHORIZED, [
                'content-type' => 'application/json',
            ]);
        }

        // TODO: получить из запроса?
        $sessionId = '12345678';

        $proto = $this->getProtocol();
        $this->initSession($proto, $sessionId);

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new Response('{"result": "Некорректный запрос"}', Response::HTTP_BAD_REQUEST, [
                'content-type' => 'application/json',
            ]);
        }

        // вызывается при регистрации MCP сервера
        if ($data['method'] === 'notifications/initialized') {
            return new Response('{"result": "OK"}', Response::HTTP_OK, [
                'content-type' => 'application/json',
            ]);
        }

        $rpcRequest = new JsonRpcRequest(...$data);
        $proto->processMessage($rpcRequest, $sessionId);

        // @phpstan-ignore-next-line
        return new Response($this->transport->responseBody, Response::HTTP_OK, [
            'content-type' => 'application/json',
        ]);
    }

    private function getProtocol(): Protocol
    {
        $this->transport = new class() extends StreamableHttpServerTransport {
            public string $responseBody;

            public function sendMessage(Message $message, string $sessionId, array $context = []): PromiseInterface
            {
                $this->responseBody = (string) json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                $resolve = fn (): HttpResponse => new HttpResponse(200, [
                    'Content-Type' => 'application/json',
                ], $this->responseBody . "\n");
                // @phpstan-ignore-next-line
                return new Promise($resolve);
            }
        };

        $proto = $this->server->getProtocol();
        $proto->bindTransport($this->transport);
        return $proto;
    }

    private function initSession(Protocol $proto, string $sessionId): void
    {
        $proto->handleClientConnected($sessionId);
        $proto->processMessage(new Notification(
            jsonrpc: '2.0',
            method: 'notifications/initialized',
        ), $sessionId);
    }
}
