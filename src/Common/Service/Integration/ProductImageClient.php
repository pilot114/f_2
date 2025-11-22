<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use App\Common\DTO\ProductImageSize;
use App\Common\Exception\ProductImageException;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Сервис для получения списка главных изображений мастер и рецептурных кодов
 * В данный момент нигде не используется
 */
class ProductImageClient
{
    private const CHUNK_SIZE = 200;
    private const BASE_URI = 'http://192.168.6.68';
    private const API_ENDPOINT = '/image/list/';

    private string $country = 'ru';

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * Установить страну (по умолчанию 'ru')
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Получить список изображений
     * если нужного размера нет, @see StaticClient::getResizeUrl
     * @param Enumerable<int, string> $codes [0 => '500020', 1 => '500632']
     * @return Collection<string, string> [code => URL]
     * @throws ProductImageException
     */
    public function get(Enumerable $codes, ProductImageSize $size = ProductImageSize::NOSIZE): Collection
    {
        if ($codes->isEmpty()) {
            return collect();
        }

        /** @var Collection<string, string> $result */
        $result = collect();

        foreach ($codes->unique()->chunk(self::CHUNK_SIZE) as $chunk) {
            $chunkCollection = collect($chunk->all());

            $imagesData = $this->fetchImages($chunkCollection);

            $result = $result->union(
                $this->processChunk($imagesData, $chunkCollection, $size)
            );
        }

        return $result;
    }

    /**
     * @param Collection<int, string> $codes
     * @return array<int, array<string, mixed>>
     * @throws ProductImageException
     */
    private function fetchImages(Collection $codes): array
    {
        $url = $this->buildUrl($codes);

        try {
            $response = $this->client->request('GET', $url, [
                'base_uri' => self::BASE_URI,
                'headers'  => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getContent(), true);

            if (!is_array($data)) {
                throw new ProductImageException("Не удалось получить данные картинки для " . implode(", ", $codes->toArray()));
            }

            return $data['images'] ?? [];
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new ProductImageException($e->getMessage());
        }
    }

    /**
     * Обработать один чанк данных
     *
     * @param array<int, array<string, mixed>> $rawImages
     * @param Collection<int, string> $allowedcodes
     * @return Collection<string, string>
     */
    private function processChunk(array $rawImages, Collection $allowedcodes, ProductImageSize $size): Collection
    {
        $result = collect([]);

        foreach ($rawImages as $img) {
            if (!$this->isValidImageItem($img)) {
                continue;
            }

            /** @var string $urlDefault */
            $urlDefault = $img['url'];
            $sizes = is_array($img['sizes']) ? $img['sizes'] : [];

            $urlForSize = function (ProductImageSize $s) use ($sizes, $urlDefault): string {
                if ($s->isNoSize()) {
                    return $urlDefault;
                }

                return $sizes[$s->value] ?? $urlDefault;
            };

            /** @var array<int, string> $sku */
            $sku = $img["sku"];
            foreach ($sku as $code) {
                if (is_string($code) && $allowedcodes->contains($code)) {
                    $result->put($code, $urlForSize($size));
                }
            }
        }

        return $result;
    }

    private function isValidImageItem(mixed $img): bool
    {
        return is_array($img)
            && !empty($img['url'])
            && is_bool($img['isMain'])
            && is_bool($img['isActive'])
            && is_array($img['sku'])
            && ($img['isMain'] || $img['isActive']);
    }

    /**
     * Построить URL с параметрами
     *
     * @param Collection<int, string> $codes
     */
    private function buildUrl(Collection $codes): string
    {
        $params = ['countryCode=' . $this->country];

        foreach ($codes as $code) {
            $params[] = "sku[]={$code}";
        }

        return self::API_ENDPOINT . '?' . implode('&', $params);
    }
}
