<?php

declare(strict_types=1);

namespace App\Common\Service\File;

use App\Domain\Portal\Files\Enum\AllowedImageType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class ImageBase64
{
    public const MAX_IMAGE_SIZE = 1024 * 1024 * 8;

    public function __construct(
        protected TempFileRegistry $tempFileRegistry
    ) {
    }

    /**
     * Метод сохраняет base64 изображение во временный файл
     */
    public function baseToFile(string $base64Image, int $maxFileSize = self::MAX_IMAGE_SIZE): File
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);

        if (count($matches) < 2) {
            throw new BadRequestHttpException('это не изображение в формате base64');
        }

        $extension = AllowedImageType::tryFrom($matches[1]);

        if (!$extension instanceof AllowedImageType) {
            throw new UnsupportedMediaTypeHttpException('не подходящий формат изображения');
        }

        /** @var string $clearBase64 */
        $clearBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $imageDecoded = base64_decode($clearBase64, true);

        if ($imageDecoded === false) {
            throw new BadRequestHttpException('Невалидный base64');
        }

        $size = $this->getImageSize($imageDecoded);
        if ($size > $maxFileSize) {
            throw new BadRequestHttpException('максимальный размер файла - ' . static::MAX_IMAGE_SIZE / 1024 / 1024 . ' МБ');
        }
        return $this->tempFileRegistry->createFile($imageDecoded);
    }

    protected function getImageSize(string $imageDecoded): int
    {
        return strlen($imageDecoded);
    }
}
