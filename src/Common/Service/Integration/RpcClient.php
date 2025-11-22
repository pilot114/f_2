<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use Database\Connection\CpConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RpcClient
{
    public function __construct(
        private HttpClientInterface $client,
        private CpConfig $config,
        private Request $request,
    ) {
    }

    public function call(string $method, array $params = []): mixed
    {
        $scheme = isset($_SERVER['HTTP_HTTPS']) ? "https://" : "http://";

        $host = $this->request->getHost();
        $token = $this->request->headers->get('Authorization') ?? '';

        $response = $this->client->request('POST', '/api/v1/rpc', [
            'base_uri' => $scheme . $host,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => $token,
                'X-Is-Prod-DB'  => $this->config->isProd ? 'true' : 'false',
                'X-Is-New-Back' => 'true',
            ],
            'json' => [
                'jsonrpc' => '2.0',
                'method'  => $method,
                'params'  => $params,
                'id'      => uniqid(),
            ],
        ]);
        $data = (array) json_decode($response->getContent(), true);
        return $data['result'];
    }
}
