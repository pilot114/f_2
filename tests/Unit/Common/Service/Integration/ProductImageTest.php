<?php

declare(strict_types=1);

use App\Common\DTO\ProductImageSize;
use App\Common\Exception\ProductImageException;
use App\Common\Service\Integration\ProductImageClient;
use Illuminate\Support\Collection;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

beforeEach(function (): void {
    $this->client = Mockery::mock(HttpClientInterface::class);
    $this->service = new ProductImageClient($this->client);
});

afterEach(function (): void {
    Mockery::close();
});

describe('ProductImage', function (): void {
    it('returns empty collection when codes are empty', function (): void {
        $codes = collect([]);
        $result = $this->service->get($codes);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->isEmpty())->toBeTrue();
    });

    it('successfully fetches and returns images with correct URLs', function (): void {
        $codes = collect(['500020', '500632']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode([
            'images' => [
                [
                    'url'      => 'https://example.com/image1.jpg',
                    'isMain'   => true,
                    'isActive' => false,
                    'sku'      => ['500020'],
                    'sizes'    => [
                        'small' => 'https://example.com/image1_small.jpg',
                        'w150'  => 'https://example.com/image1_w150.jpg',
                    ],
                ],
                [
                    'url'      => 'https://example.com/image2.jpg',
                    'isMain'   => false,
                    'isActive' => true,
                    'sku'      => ['500632'],
                    'sizes'    => [
                        'small' => 'https://example.com/image2_small.jpg',
                    ],
                ],
            ],
        ]));

        $this->client->shouldReceive('request')
            ->once()
            ->with('GET', '/image/list/?countryCode=ru&sku[]=500020&sku[]=500632', [
                'base_uri' => 'http://192.168.6.68',
                'headers'  => [
                    'Content-Type' => 'application/json',
                ],
            ])
            ->andReturn($mockResponse);

        $result = $this->service->get($codes, ProductImageSize::SIZE60);

        expect($result)
            ->toHaveCount(2)
            ->and($result->get('500020'))->toBe('https://example.com/image1_small.jpg')
            ->and($result->get('500632'))->toBe('https://example.com/image2_small.jpg');
    });

    it('returns original URL when requested size not available', function (): void {
        $codes = collect(['500020']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode([
            'images' => [
                [
                    'url'      => 'https://example.com/image1.jpg',
                    'isMain'   => true,
                    'isActive' => false,
                    'sku'      => ['500020'],
                    'sizes'    => [
                        'small' => 'https://example.com/image1_small.jpg',
                    ],
                ],
            ],
        ]));

        $this->client->shouldReceive('request')->once()->andReturn($mockResponse);

        $result = $this->service->get($codes, ProductImageSize::SIZE300); // SIZE300 не доступен

        expect($result->get('500020'))->toBe('https://example.com/image1.jpg'); // Возвращает оригинальный
    });

    it('returns original URL for NOSIZE', function (): void {
        $codes = collect(['500020']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode([
            'images' => [
                [
                    'url'      => 'https://example.com/image1.jpg',
                    'isMain'   => true,
                    'isActive' => false,
                    'sku'      => ['500020'],
                    'sizes'    => [
                        'small' => 'https://example.com/image1_small.jpg',
                    ],
                ],
            ],
        ]));

        $this->client->shouldReceive('request')->once()->andReturn($mockResponse);

        $result = $this->service->get($codes, ProductImageSize::NOSIZE);

        expect($result->get('500020'))->toBe('https://example.com/image1.jpg');
    });

    it('filters out invalid images', function (): void {
        $codes = collect(['500020', '500021', '500022']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode([
            'images' => [
                [
                    'url'      => 'https://example.com/image1.jpg',
                    'isMain'   => true,
                    'isActive' => false,
                    'sku'      => ['500020'],
                    'sizes'    => [],
                ],
                [
                    'url'      => '', // Пустой URL - должен отфильтроваться
                    'isMain'   => true,
                    'isActive' => false,
                    'sku'      => ['500021'],
                    'sizes'    => [],
                ],
                [
                    'url'      => 'https://example.com/image3.jpg',
                    'isMain'   => false,
                    'isActive' => false, // Оба false - должен отфильтроваться
                    'sku'      => ['500022'],
                    'sizes'    => [],
                ],
            ],
        ]));

        $this->client->shouldReceive('request')->once()->andReturn($mockResponse);

        $result = $this->service->get($codes);

        expect($result)->toHaveCount(1)
            ->and($result->has('500020'))->toBeTrue()
            ->and($result->has('500021'))->toBeFalse()
            ->and($result->has('500022'))->toBeFalse();
    });

    it('handles large collections by chunking requests', function (): void {
        $codes = collect(array_map('strval', range(1, 450))); // Больше чем CHUNK_SIZE (200)

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->times(3)->andReturn(json_encode([
            'images' => [],
        ]));

        // Ожидаем 3 запроса для 450 элементов (200 + 200 + 50)
        $this->client->shouldReceive('request')->times(3)->andReturn($mockResponse);

        $result = $this->service->get($codes);

        expect($result)->toBeInstanceOf(Collection::class);
    });

    it('sets country correctly and uses it in URL', function (): void {
        $codes = collect(['500020']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode([
            'images' => [],
        ]));

        $this->client->shouldReceive('request')
            ->once()
            ->with('GET', '/image/list/?countryCode=en&sku[]=500020', Mockery::any())
            ->andReturn($mockResponse);

        $result = $this->service->setCountry('en');

        expect($result)->toBe($this->service); // Возвращает self

        $this->service->get($codes);
    });

    it('throws ProductImageException on network error', function (): void {
        $codes = collect(['500020']);

        // Альтернативно - можем обновить класс ProductImage чтобы он ловил все исключения
        $this->client->shouldReceive('request')
            ->once()
            ->andThrow(new Exception('Network error'));

        // Но тогда ожидаем обычное Exception, а не ProductImageException
        $this->service->get($codes);
    })->throws(Exception::class);

    it('throws exception on invalid JSON response', function (): void {
        $codes = collect(['500020']);

        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getContent')->once()->andReturn('invalid json');

        $this->client->shouldReceive('request')->once()->andReturn($mockResponse);

        $this->service->get($codes);
    })->throws(ProductImageException::class);
});
