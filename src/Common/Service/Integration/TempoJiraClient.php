<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TempoJiraClient
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function getPlanned(): array
    {
        $url = 'https://jira.siberianhealth.com/rest/tempo-planning/2/resource-planning/search';

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Cookie'       => 'seraph.rememberme.cookie=19100%3Ac11f21632f6834873788ce60e73bcb3b3676cc96',
            ],
            'json' => [
                'from'     => '2025-07-20',
                'to'       => '2025-08-23',
                'generic'  => [],
                'roles'    => [],
                'teams'    => [],
                'users'    => [],
                'page'     => 1,
                'pageSize' => 50,
            ],
        ]);

        return $response->toArray();
    }

    public function getPlannedUsers(): array
    {
        $users = json_decode(
            '{"keys":["ug:0def6298-a1da-4408-af5d-3afcca39adf6","JIRAUSER14612","ug:15b3c984-30c3-4214-96bf-d6cb8aef7d41","ug:29ff6c0b-a180-4395-a9e7-61ef92c0dd79","f8a9ee91-366e-40f2-b48c-f22a335179b6","ug:06b9bd0a-a7dd-445a-83a2-c1bb2735c854","ug:09a806d3-1489-4d8c-897c-71066a513a81","ug:57487617-4556-41ab-9e71-75955a8c3dfd","05cb56b3-c369-4289-9fc2-bdfbe24891cf","JIRAUSER16634","ug:4aea6968-7366-4483-8526-5ad03a83ee2f"]}',
            true
        );
        unset($users['keys'][2]);

        $url = 'https://jira.siberianhealth.com/rest/tempo-core/1/users/searchByKey';

        $response = $this->client->request('POST', $url, [
            // Передаем JSON-данные
            'json' => [
                'keys' => array_values($users['keys']),
            ],
            // Заголовки, включая куки
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin'       => 'https://jira.siberianhealth.com',
                'Cookie'       => 'seraph.rememberme.cookie=19100%3Ac11f21632f6834873788ce60e73bcb3b3676cc96',
            ],
        ]);

        // Получаем тело ответа
        $responseBody = $response->getContent();
        $statusCode = $response->getStatusCode();

        return [
            'status' => $statusCode,
            'body'   => json_decode($responseBody),
        ];
    }
}
