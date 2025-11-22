<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Controller;

use Intervention\Image\Image;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbstractController
{
    protected function buildJsonResponse(mixed $result): JsonResponse
    {
        return new JsonResponse([
            'result' => $result,
        ], Response::HTTP_OK);
    }

    /**
     * @param bool $isInline
     *  - если true, браузер попробует показать файл
     *  - если false, браузер предложит скачать файл
     */
    protected function buildStreamedResponse(string $url, string $name, array $cacheControl = [], bool $isInline = false): StreamedResponse
    {
        $outputStream = fopen('php://output', 'wb');
        $fileStream = fopen($url, 'rb');

        if ($outputStream === false || $fileStream === false) {
            throw new RuntimeException('Не удалось открыть поток для чтения файла или записи в вывод.');
        }

        $streamedResponse = new StreamedResponse(function () use ($outputStream, $fileStream): void {
            stream_copy_to_stream($fileStream, $outputStream);
            fclose($fileStream);
            fclose($outputStream);
        });

        $mimeType = 'application/octet-stream';
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $mimeType = trim(substr($header, strlen('Content-Type:')));
                break;
            }
        }

        return $this->addHeaderToStreamedResponse($streamedResponse, $mimeType, $name, $isInline, $cacheControl);
    }

    protected function buildResizedImageStreamedResponse(Image $image, string $name, array $cacheControl = [], bool $isInline = false): StreamedResponse
    {
        $mimeType = $image->mime();

        $streamedResponse = new StreamedResponse(function () use ($image): void {
            try {
                echo $image->encode();
            } finally {
                $image->destroy();
            }
        });

        return $this->addHeaderToStreamedResponse($streamedResponse, $mimeType, $name, $isInline, $cacheControl);
    }

    private function addHeaderToStreamedResponse(StreamedResponse $streamedResponse, string $mimeType, string $name, bool $isInline, array $cacheControl): StreamedResponse
    {
        $streamedResponse->headers->set('Content-Type', $mimeType);

        $disposition = $isInline ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        $streamedResponse->headers->set('Content-Disposition', "$disposition; filename=\"$name\"");

        if ($cacheControl !== []) {
            $streamedResponse->setCache($cacheControl);
        }

        return $streamedResponse;
    }
}
