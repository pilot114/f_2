<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UseDeskHttpClient
{
    private string $host = 'http://192.168.6.170';

    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    private function call(string $method, string $url, array $params = []): array
    {
        $response = $this->client->request($method, $url, [
            'base_uri' => $this->host,
            'headers'  => [
                'Content-Type' => 'application/json',
            ],
            ...$params,
        ]);

        return (array) json_decode($response->getContent(), true);
    }

    public function getChats(array $params): array
    {
        $query = http_build_query($params);

        return $this->call('GET', '/api/v1/usedesk/chats' . ($query === '' || $query === '0' ? '' : "?$query"));
    }

    public function getChatMessages(int $id, array $params): array
    {
        $query = http_build_query($params);

        return $this->call('GET', '/api/v1/usedesk/chats/' . $id . '/messages' . ($query === '' || $query === '0' ? '' : "?$query"));
    }
}
