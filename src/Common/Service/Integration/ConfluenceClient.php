<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ConfluenceClient
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function getContent(int $contentId): string
    {
        $response = $this->client->request('GET', "/rest/api/content/$contentId", [
            'base_uri' => 'https://docs.siberianhealth.com',
            'query'    => [
                'expand' => 'body.storage,version,space',
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getContent(), true);

        if (!is_array($data)) {
            return '';
        }

        return $data['body']['storage']['value'] ?? '';
    }
}
