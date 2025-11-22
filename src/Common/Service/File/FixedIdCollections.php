<?php

declare(strict_types=1);

namespace App\Common\Service\File;

use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use InvalidArgumentException;

class FixedIdCollections
{
    public static function check(string $collectionName, ?int $idInCollection = null): void
    {
        $list = [
            File::USERPIC_COLLECTION, //Аватарки пользователей
            MemoryPagePhotoService::OTHER_IMAGE_COLLECTION, // Дополнительные картинки в страницах памяти
            MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION, // Картинки в комментариях к странице памяти
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION, // Главное изображение в странице памяти
            'task2', // файлы в задачах
        ];

        if (in_array($collectionName, $list, true) && $idInCollection === null) {
            throw new InvalidArgumentException('idInCollection обязателен и не может быть сгенерирован автоматически в коллекции ' . $collectionName);
        }
    }
}
