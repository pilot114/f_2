<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Integration;

use App\Common\Service\Integration\ConfluenceClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->client = new ConfluenceClient($this->httpClient);
});

afterEach(function (): void {
    Mockery::close();
});

it('имеет зависимость от HttpClientInterface', function (): void {
    $reflection = new ReflectionClass(ConfluenceClient::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('client')
        ->and($parameters[0]->getType()?->getName())->toBe('Symfony\Contracts\HttpClient\HttpClientInterface');
});

it('имеет метод getContent', function (): void {
    $reflection = new ReflectionClass(ConfluenceClient::class);

    expect($reflection->hasMethod('getContent'))->toBeTrue();

    $method = $reflection->getMethod('getContent');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('string');
});

it('выполняет GET запрос к Confluence API и возвращает содержимое страницы', function (): void {
    $contentId = 12345;
    $expectedContent = '<p>Тестовое содержимое страницы Confluence</p>';

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns(json_encode([
        'body' => [
            'storage' => [
                'value' => $expectedContent,
            ],
        ],
    ]));

    $this->httpClient
        ->expects('request')
        ->with('GET', '/rest/api/content/12345', Mockery::on(function (array $config): bool {
            return $config['base_uri'] === 'https://docs.siberianhealth.com'
                && $config['query']['expand'] === 'body.storage,version,space'
                && $config['headers']['Accept'] === 'application/json';
        }))
        ->andReturns($response);

    $result = $this->client->getContent($contentId);

    expect($result)->toBe($expectedContent);
});

it('возвращает пустую строку когда body отсутствует в ответе', function (): void {
    $contentId = 12345;

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns(json_encode([
        'id'    => '12345',
        'title' => 'Test Page',
    ]));

    $this->httpClient
        ->expects('request')
        ->with('GET', '/rest/api/content/12345', Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getContent($contentId);

    expect($result)->toBe('');
});

it('возвращает пустую строку когда storage отсутствует в ответе', function (): void {
    $contentId = 12345;

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns(json_encode([
        'body' => [
            'view' => [
                'value' => 'some content',
            ],
        ],
    ]));

    $this->httpClient
        ->expects('request')
        ->with('GET', '/rest/api/content/12345', Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getContent($contentId);

    expect($result)->toBe('');
});

it('возвращает пустую строку когда value отсутствует в ответе', function (): void {
    $contentId = 12345;

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns(json_encode([
        'body' => [
            'storage' => [
                'representation' => 'storage',
            ],
        ],
    ]));

    $this->httpClient
        ->expects('request')
        ->with('GET', '/rest/api/content/12345', Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getContent($contentId);

    expect($result)->toBe('');
});

it('возвращает пустую строку когда json_decode возвращает не массив', function (): void {
    $contentId = 12345;

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns('invalid json');

    $this->httpClient
        ->expects('request')
        ->with('GET', '/rest/api/content/12345', Mockery::type('array'))
        ->andReturns($response);

    $result = $this->client->getContent($contentId);

    expect($result)->toBe('');
});

it('корректно обрабатывает различные ID страниц', function (): void {
    $testCases = [
        1      => 'Content for page 1',
        999999 => 'Content for page 999999',
        42     => 'The answer',
    ];

    foreach ($testCases as $contentId => $content) {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('getContent')->andReturns(json_encode([
            'body' => [
                'storage' => [
                    'value' => $content,
                ],
            ],
        ]));

        $this->httpClient
            ->expects('request')
            ->with('GET', "/rest/api/content/{$contentId}", Mockery::type('array'))
            ->andReturns($response);

        $result = $this->client->getContent($contentId);

        expect($result)->toBe($content);
    }
});

it('использует правильные параметры запроса', function (): void {
    $contentId = 12345;

    $response = Mockery::mock(ResponseInterface::class);
    $response->expects('getContent')->andReturns(json_encode([
        'body' => [
            'storage' => [
                'value' => 'test',
            ],
        ],
    ]));

    $this->httpClient
        ->expects('request')
        ->with(
            'GET',
            '/rest/api/content/12345',
            Mockery::on(function ($config): bool {
                return isset($config['base_uri'])
                    && $config['base_uri'] === 'https://docs.siberianhealth.com'
                    && isset($config['query'])
                    && $config['query']['expand'] === 'body.storage,version,space'
                    && isset($config['headers'])
                    && $config['headers']['Accept'] === 'application/json';
            })
        )
        ->andReturns($response);

    $this->client->getContent($contentId);
});
